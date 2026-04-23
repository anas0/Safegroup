jQuery(document).ready(function ($){
    const cookieBar = $('#siteseo-cookie-bar');
    const backdrop = $('#siteseo-cookie-bar-backdrop');
    const acceptBtn = $('#siteseo-cookie-bar-accept');
    const closeBtn = $('#siteseo-cookie-bar-close');
    const manageBtn = $('#siteseo-cookie-bar-manage-btn');
    const cookieName = 'cookieConsent';
    const cookieDuration = 30 * 24 * 60 * 60;

    function setCookie(name, value, duration){
        const date = new Date();
        date.setTime(date.getTime() + duration * 1000);
        document.cookie = `${name}=${value}; path=/; expires=${date.toUTCString()}`;
    }

    function getCookie(name){
        const cookies = document.cookie.split(';');
        for(let i = 0; i < cookies.length; i++){
            const cookie = cookies[i].trim();
            if(cookie.indexOf(name + '=') === 0){
                return cookie.substring(name.length + 1);
            }
        }
        return null;
    }

    //backdrop
    function hideCookieBar(){
        cookieBar.hide();
        backdrop.hide();
        manageBtn.show();
    }

    //show cookie bar 
    function showCookieBar(){
        cookieBar.show();
        if(cookieBar.hasClass('siteseo-cookie-bar-middle')){
            backdrop.show();
        }
    }

    if(!getCookie(cookieName)){
        showCookieBar();
    }

    // Accept btn
    acceptBtn.on('click', function(e){
        e.preventDefault();
        setCookie(cookieName, 'true', cookieDuration);
        hideCookieBar();
    });

    // Close btn
    closeBtn.on('click', function(e){
        e.preventDefault();
        hideCookieBar();
    });

    // Manage btn
    manageBtn.on('click', function (e){
        e.preventDefault();
        showCookieBar();
    });
});