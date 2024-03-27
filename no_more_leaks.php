<?php
/**
 * No More Leaks plugin for Roundcube
 *
 * @version 0.5
 * @author Sander Smeenk <sander@bit.nl>
 * @url https://github.com/bitnl/no-more-leaks/
 */

class no_more_leaks extends rcube_plugin {
    /**
     * @return false|void
     */
    function init(): void {
        $this->load_config();

        $rcmail = rcmail::get_instance();
        if (! $rcmail->config->get('nml_enabled', false)) {
            $this->dprint("no_more_leaks was disabled in configuration");
            return;
        }

        $this->add_hook('authenticate', array($this, 'authenticate_hook'));
        $this->add_hook('login_after', array($this, 'login_after_hook'));
    }

    /**
     * @param $args
     * @return $args
     *
     * Runs before the user login on the IMAP server is performed.
     * The user is not authenticated at this moment, but this is
     * where we can capture the password from the login action.
     */
    function authenticate_hook(array $args): array {
        $this->dprint("authenticate_hook() runs");
        $auth_user = Normalizer::normalize(strtolower($args['user']), Normalizer::FORM_C);
        $auth_pass = Normalizer::normalize($args['pass'], Normalizer::FORM_C);
        if (! $auth_user or ! $auth_pass) {
            $this->dprint("no_more_leaks normalize() failed");
            return $args;
        }

        $nml_hash = hash('sha256', $auth_user . $auth_pass);
        $this->dprint("authenticate_hook() hash: " . $nml_hash);
        unset($auth_user, $auth_pass);

        // Extract NML config options to pass to lookup class
        $nml_config = array();
        $rcmail = rcmail::get_instance();
        foreach ($rcmail->config->all() as $key => $value) {
            if (str_starts_with($key, 'nml_')) {
                $nml_config[$key] = $value;
            }
        }

        try {
            include 'check_leak.php';
            $check_leak = new Check_Leak();
            $t_diff = -hrtime(true);
            $leak_status = $check_leak->RunCheck($nml_config, $nml_hash);
            $t_diff = ($t_diff + hrtime(true)) / 1e+6; //nano to milli seconds
            $this->dprint("RunCheck() result '$leak_status' in {$t_diff}msec");
        } catch (\Throwable $e) {
            $this->dprint("authenticate_hook() Check_Leak() error: " . $e->GetMessage(), $always = true);
            $leak_status = false;
        }

        $_SESSION['no_more_leaks_found_leak'] = $leak_status;

        return $args;
    }

    /**
     * @param $args
     * @return $args
     *
     * Runs after a user successfully authenticated to the mail server.
     */
    function login_after_hook(array $args): array {
        $this->dprint("login_after_hook() runs");

        // Early exit if no leak was found.
        if (! array_key_exists('no_more_leaks_found_leak', $_SESSION) or ! $_SESSION['no_more_leaks_found_leak']) {
            $this->dprint("login_after_hook() no_more_leaks_found_leak was not defined or false");
            return $args;
        }

        $this->dprint("login_after_hook() no_more_leaks_found_leak was true", $always = true);

        $rcmail = rcmail::get_instance();
        if ($rcmail->config->get('nml_invalidate_session_when_leaked', true)) {
            $this->dprint("login_after_hook() user's session killed");
            $rcmail->kill_session();
        }
        if ($rcmail->config->get('nml_redirect_when_leaked', false)) {
            $this->dprint("login_after_hook() redirecting");
            header('Location: ' . $rcmail->config->get('nml_redirect_destination'), true, 303);
            exit;
        }

        $this->dprint("login_after_hook() plugin template page");
        $rcmail->output->send('no_more_leaks.leak_found');
    }

    /**
     * @param $message
     * @return false|void
     *
     * Logs a message to the PHP error_log(), if configured to debug.
     */
    private function dprint(string $message, bool $always = false): void {
        $rcmail = rcmail::get_instance();
        if ($always or $rcmail->config->get('nml_debug', false)) {
            $pre = "no_more_leaks | remote_addr:" . $_SERVER['REMOTE_ADDR'];
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $pre .= "|x_forwarded_for:" . $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
                $pre .= "|x_real_ip:" . $_SERVER['HTTP_X_REAL_IP'];
            }
            error_log($pre . " | " . $message);
        }
    }
}
