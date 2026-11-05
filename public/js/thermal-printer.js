/*
 * Prints receipts straight to the 58mm thermal printer (Officom OC-58IIH, ESC/POS) over USB,
 * with no printer driver and no print dialog — Chrome/Edge's WebUSB talks to the printer itself.
 *
 * One-time setup on the cashier PC: switch the printer's USB driver to WinUSB with Zadig,
 * otherwise Windows keeps the printer to itself and the browser can't reach it.
 *
 * How a receipt is printed:
 *   1. the receipt page draws itself as a picture 384 dots wide (the print head's width);
 *   2. the picture is turned into black-or-white dots;
 *   3. the dots are sent as ESC/POS "print this image" commands, then the paper is fed and cut.
 */
window.ThermalPrinter = (function () {
    const DOTS_WIDE = 384;              // 48mm printable width at 203 dpi
    const BYTES_PER_ROW = DOTS_WIDE / 8;
    /* The printer's memory is small and it drops bytes (instead of saying "wait") when it fills up,
       which slants and scrambles the picture. So the image goes in thin strips, each followed by a
       pause about as long as the printer takes to print it (90mm/s, 8 dots per mm). */
    const BAND_ROWS = 24;
    const MS_PER_ROW = 1000 / (90 * 8);   // ≈1.4ms to print one row of dots
    const USB_PRINTER_CLASS = 0x07;
    const INK_THRESHOLD = 128;            // darker than mid-grey → black dot (same cut-off as the approved test print)
    const sleep = ms => new Promise(r => setTimeout(r, ms));

    let device = null;
    let endpointOut = null;
    let interfaceNumber = null;

    function isSupported() {
        return 'usb' in navigator;
    }

    /* Finds the printer: one the browser was already allowed to use, or (first time only)
       asks the cashier to pick it. The picking must happen right after a click. */
    async function findPrinter() {
        const known = await navigator.usb.getDevices();
        const printer = known.find(d => hasPrinterInterface(d)) || known[0];
        if (printer) return printer;
        return navigator.usb.requestDevice({ filters: [{ classCode: USB_PRINTER_CLASS }] });
    }

    function hasPrinterInterface(dev) {
        return (dev.configurations || []).some(c => c.interfaces.some(i =>
            i.alternates.some(a => a.interfaceClass === USB_PRINTER_CLASS)));
    }

    /* Connects for one print. Windows lets only one tab/program use the printer at a time, so
       the connection is closed again right after printing (disconnect) — otherwise a tab that
       printed once would keep every other tab, and the live site, locked out. */
    async function connect() {
        device = await findPrinter();
        try {
            if (!device.opened) await device.open();
        } catch (err) {
            const busy = new Error('The printer is busy: another browser tab or program is using it. '
                + 'Close the other tabs of the app (or restart the browser) and try again.');
            busy.name = 'PrinterBusyError';
            throw busy;
        }
        if (device.configuration === null) await device.selectConfiguration(1);

        // The interface that has a "bulk OUT" endpoint is the one that accepts print data
        endpointOut = null;
        for (const iface of device.configuration.interfaces) {
            for (const alt of iface.alternates) {
                const out = alt.endpoints.find(e => e.direction === 'out' && e.type === 'bulk');
                if (out) {
                    interfaceNumber = iface.interfaceNumber;
                    endpointOut = out.endpointNumber;
                    break;
                }
            }
            if (endpointOut) break;
        }
        if (!endpointOut) throw new Error('This USB device does not look like a printer.');

        await device.claimInterface(interfaceNumber);
    }

    async function disconnect() {
        if (!device) return;
        try {
            if (device.opened) {
                if (interfaceNumber !== null) await device.releaseInterface(interfaceNumber).catch(() => {});
                await device.close();
            }
        } catch (e) { /* already unplugged/closed — nothing to free */ }
        device = null; endpointOut = null; interfaceNumber = null;
    }

    /* Turns the picture into ESC/POS bytes: 1 bit per dot, 1 = burn (black).
       The picture must be exactly DOTS_WIDE wide (prepareImage makes sure of it). */
    function encodeImage(canvas) {
        const width = DOTS_WIDE;
        const height = canvas.height;
        const pixels = canvas.getContext('2d').getImageData(0, 0, width, height).data;

        const parts = [new Uint8Array([0x1B, 0x40])];   // ESC @ — reset the printer

        for (let top = 0; top < height; top += BAND_ROWS) {
            const rows = Math.min(BAND_ROWS, height - top);
            const band = new Uint8Array(8 + BYTES_PER_ROW * rows);
            // GS v 0 — print raster image: width in bytes, height in dots (little-endian)
            band.set([0x1D, 0x76, 0x30, 0x00, BYTES_PER_ROW & 0xFF, BYTES_PER_ROW >> 8, rows & 0xFF, rows >> 8]);

            for (let y = 0; y < rows; y++) {
                for (let x = 0; x < width; x++) {
                    const i = ((top + y) * width + x) * 4;
                    const brightness = 0.299 * pixels[i] + 0.587 * pixels[i + 1] + 0.114 * pixels[i + 2];
                    if (brightness < INK_THRESHOLD) band[8 + y * BYTES_PER_ROW + (x >> 3)] |= 0x80 >> (x & 7);
                }
            }
            parts.push(band);
        }

        parts.push(new Uint8Array([0x1B, 0x64, 0x04]));        // ESC d 4 — feed 4 lines past the tear bar
        parts.push(new Uint8Array([0x1D, 0x56, 0x42, 0x00]));  // GS V — cut (ignored if there's no cutter)
        return parts;
    }

    /* Fits the picture to exactly the print head's width (the browser may draw it a dot or two
       wider or narrower — rows of the wrong width make the printout slant) and cuts the blank
       rows off the bottom so no paper is wasted. */
    function prepareImage(canvas) {
        const height = Math.round(canvas.height * DOTS_WIDE / canvas.width);
        const fitted = document.createElement('canvas');
        fitted.width = DOTS_WIDE;
        fitted.height = height;
        const ctx = fitted.getContext('2d', { willReadFrequently: true });
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, DOTS_WIDE, height);
        ctx.drawImage(canvas, 0, 0, DOTS_WIDE, height);

        const data = ctx.getImageData(0, 0, DOTS_WIDE, height).data;
        let last = height - 1;
        for (; last > 0; last--) {
            const rowStart = last * DOTS_WIDE * 4;
            let ink = false;
            for (let x = 0; x < DOTS_WIDE * 4; x += 4) {
                if (data[rowStart + x] < INK_THRESHOLD) { ink = true; break; }
            }
            if (ink) break;
        }

        const trimmed = document.createElement('canvas');
        trimmed.width = DOTS_WIDE;
        trimmed.height = last + 1;
        trimmed.getContext('2d', { willReadFrequently: true }).drawImage(fitted, 0, 0);
        return trimmed;
    }

    /* receiptWindow: the receipt page's window (it provides renderThermalReceipt). */
    async function printReceipt(receiptWindow) {
        if (!isSupported()) throw new Error('This browser cannot print to USB. Please use Google Chrome or Microsoft Edge.');

        try {
            await connect();   // first, while the click still counts (needed the first time to pick the printer)
            const canvas = prepareImage(await receiptWindow.renderThermalReceipt(DOTS_WIDE));
            for (const part of encodeImage(canvas)) {
                const result = await device.transferOut(endpointOut, part);
                if (result.status !== 'ok') throw new Error('The printer did not accept the data (' + result.status + ').');
                // Image strip: give the printer time to print it before sending the next one
                if (part[0] === 0x1D && part[1] === 0x76) await sleep(part[6] * MS_PER_ROW * 1.15);
            }
        } finally {
            await disconnect();   // free the printer for other tabs
        }
    }

    // Unplugged: forget the connection so the next print reconnects
    if (isSupported()) {
        navigator.usb.addEventListener('disconnect', e => {
            if (e.device === device) { device = null; endpointOut = null; interfaceNumber = null; }
        });
    }

    // _toEscPos: the exact bytes a receipt picture becomes (used to check the output without a printer)
    return { isSupported, printReceipt, _toEscPos: canvas => encodeImage(prepareImage(canvas)) };
})();
