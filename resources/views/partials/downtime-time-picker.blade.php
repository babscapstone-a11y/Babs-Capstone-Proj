{{--
    Time field for the Set Downtime modal: clicking it opens the clock dial (partials/clock-dial).
    The hidden input keeps the 24-hour "H:i" value the server expects.
--}}
<input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
<button type="button" class="cd-field {{ $errors->has($name) ? 'has-error' : '' }}" data-picker="time" data-for="{{ $id }}"
        aria-haspopup="dialog" aria-label="{{ $label }} time" onclick="ClockDial.open(this)">
    <span class="cd-text"></span>
    <i class="fas fa-clock"></i>
</button>
