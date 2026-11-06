{{-- If the browser restores this page from its back/forward memory (e.g. Back after logging out),
     reload it so the server decides what to show. A signed-out user is then sent to the login page. --}}
<script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>
