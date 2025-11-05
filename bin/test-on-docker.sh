#!/bin/bash

docker run --rm \
	-v .:/forms-bridge \
	-w /forms-bridge \
	--add-host=host.docker.internal:host-gateway \
	--name forms-bridge-tests \
	codeccoop/wp-test \
	sh -c "
nohup docker-entrypoint.sh mariadbd >/dev/null 2>&1 &
echo -n 'Install composer dependencies:                '
composer -q install
echo '✅'
echo -n 'Wait for mariadb to start for three seconds:  '
sleep 3
echo '✅'
echo -n 'Install wordpress test suite:                 '
TMPDIR=/opt bin/install-wp-tests.sh >/dev/null 2>&1
echo '✅'
echo 'Download integrations:'
mkdir -p /tmp/wordpress/wp-content/mu-plugins
echo -n '  Contact Form 7:                             '
curl -sL --connect-timeout 10 https://downloads.wordpress.org/plugin/contact-form-7.6.1.3.zip > /tmp/contact-form-7.zip
unzip -qq /tmp/contact-form-7.zip -d /tmp/wordpress/wp-content/mu-plugins
echo '✅'
echo -n '  Ninja Forms:                                '
curl -sL --connect-timeout 10 https://downloads.wordpress.org/plugin/ninja-forms.3.13.0.zip > /tmp/ninja-forms.zip
unzip -qq /tmp/ninja-forms.zip -d /tmp/wordpress/wp-content/mu-plugins
echo '✅'
echo -n '  WPForms:                                    '
curl -sL --connect-timeout 10 https://www.codeccoop.org/formsbridge/plugins/wpforms.zip > /tmp/wpforms.zip
unzip -qq /tmp/wpforms.zip -d /tmp/wordpress/wp-content/mu-plugins
echo '✅'
echo -n '  GravityForms:                               '
curl -sL --connect-timeout 10 https://www.codeccoop.org/formsbridge/plugins/gravityforms.zip > /tmp/gravityforms.zip
unzip -qq /tmp/gravityforms.zip -d /tmp/wordpress/wp-content/mu-plugins
echo '✅'

echo 'Run tests! 🚀'
echo

vendor/bin/phpunit
"
