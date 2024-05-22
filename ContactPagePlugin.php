<?php

/**
 * Main class for contact page plugin
 * 
 * @author Joe Simpson
 * 
 * @class ContagePagePlugin
 *
 * @ingroup plugins_generic_contactPage
 *
 * @brief Contage Page
 */

namespace APP\plugins\generic\contactPage;

use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class ContactPagePlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            Hook::add( 'BlockPages::blocks', [ $this, 'blockConfigs' ] );
            Hook::add('TemplateResource::getFilename', [$this, '_overridePluginTemplates']);
            Hook::add('LoadHandler', [$this, 'setPageHandler']);
        }

        return $success;
    }

    public function setPageHandler(string $hookName, array $args): bool
    {
        $page =& $args[0];
        $handler =& $args[3];
        if ($this->getEnabled() && $page === 'contactform') {
            $handler = new ContactPageHandler($this);
            return true;
        }
        return false;
    }

    public function blockConfigs($hookName, $args) {
        $config = &$args[0];
        $config['contactForm'] = [
			'title' => 'Contact Form',
			'fields' => [],
		];
    }


    /**
     * Provide a name for this plugin
     *
     * The name will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDisplayName()
    {
        return 'Contact Page';
    }

    /**
     * Provide a description for this plugin
     *
     * The description will appear in the Plugin Gallery where editors can
     * install, enable and disable plugins.
     */
    public function getDescription()
    {
        return 'This plugin provides a contact page.';
    }

}
