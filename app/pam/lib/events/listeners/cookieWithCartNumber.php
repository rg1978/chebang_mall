<?php

class pam_events_listeners_cookieWithCartNumber {

    public function logout()
    {
        userAuth::syncCookieWithCartNumber(0);
        userAuth::syncCookieWithCartVariety(0);

        return true;
    }
}
