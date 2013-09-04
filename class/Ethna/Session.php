<?php
namespace Ethna;

/**
 *  Session
 *
 *  @author   Junya Hayashi <junya.hayashi@gree.net>
 */
class Session
{
    protected $is_alive = false;

    /**
     *  get session name
     */
    public function getSessionName()
    {
        return session_name();
    }

    /**
     *  get session id
     *  Note: session_id is still available even if you close the session.
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     *  start session
     */
    public function start()
    {
        if (! $this->isAlive()) {
            session_start();
            $this->is_alive = true;
        }
    }

    /**
     *  write close session
     *  write data and send cookie (if we use cookie for session) to client
     */
    public function writeClose()
    {
        if ($this->isAlive()) {
            session_write_close();
            $this->is_alive = false;
        }
    }

    /**
     *  regenerate id
     */
    public function regenerateId()
    {
        if ($this->isAlive()) {
            session_regenerate_id(true);
        }
    }

    /**
     *  restore session
     */
    public function restore()
    {
        if (! empty($_COOKIE[$this->getSessionName()])) {
            $this->start();
            $this->regenerateId();
        }
    }

    /**
     *  destroy session including global variable and cookie
     *  we can destroy the session only when it is alive.
     */
    public function destroy()
    {
        if ($this->isAlive()) {
            $this->clearGlobalSessionVariable();
            $this->removeCookie();
            session_destroy();
            $this->is_alive = false;
        }
    }

    /**
     *  isAlive: session is started and not closed/destroyed
     */
    public function isAlive()
    {
        return $this->is_alive;
    }

    /**
     *  get value from session
     */
    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     *  set value to session
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     *  remove value from session
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     *  clear $_SESSION variable
     */
    protected function clearGlobalSessionVariable()
    {
        $_SESSION = array();
    }

    /**
     *  remove cookie
     */
    protected function removeCookie()
    {
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                $this->getSessionName(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
}
