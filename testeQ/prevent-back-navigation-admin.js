(function() {
    history.pushState(null, null, location.href);
    
    window.onpopstate = function () {
        history.pushState(null, null, location.href);
        window.location.replace('admin-logout.php');
    };
    
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.replace('admin-logout.php');
        }
    });
    
    if (window.performance && window.performance.navigation.type === 2) {
        window.location.replace('admin-logout.php');
    }
})();
