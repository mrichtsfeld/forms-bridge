<?php

namespace FORMS_BRIDGE;

if (!defined('ABSPATH')) {
    exit();
}

class Google_Sheets_Credential extends Oauth_Credential
{
    protected const transient = 'forms-bridge-gsheets-credential';

    protected function oauth_service_url($verb)
    {
        if ($verb === 'auth') {
            return 'https://accounts.google.com/o/oauth2/v2/auth';
        }

        return "https://oauth2.googleapis.com/{$verb}";
    }

    public function oauth_grant()
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_credential');
        }

        if ($this->authorized) {
            $result = $this->revoke_token();

            if (!$result) {
                return new WP_Error('internal_server_error');
            }

            return;
        }

        set_transient(self::transient, $this->data, 600);

        ob_start();
        ?>
<form method="POST" action="<?php echo $this->oauth_service_url('auth'); ?>">
	<input name="client_id" value="<?php echo $this->client_id; ?>" />
	<input name="scope" value="<?php echo $this->scope; ?>" />
	<input name="response_type" value="code" />
	<input name="redirect_uri" value="<?php echo $this->redirect_uri(); ?>" />
	<input name="access_type" value="offline" />
</form>
		<?php
  $form = ob_get_clean();

  return ['form' => $form];
    }
}
