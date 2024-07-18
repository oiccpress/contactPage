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

            Hook::add( 'Form::config::after', [$this, 'contextSettings'] );
            Hook::add( 'Schema::get::context', [ $this, 'addToContextSchema' ] );
            Hook::add( 'Context::edit', [ $this, 'editContext' ] );
        }

        return $success;
    }

    // Add the journal stats to the schema return
    public function addToContextSchema(string $hookName, array $args): bool
    {
        $schema = &$args[0];

        $schema->properties->{"contactFormEmail"} = (object)[
            'type' => 'string',
            'multilingual' => false,
            'apiSummary' => true,
            'validation' => ['nullable'],
        ];

        return false;

    }

    // Save the Journal stats
    public function editContext(string $hookName, array $args): void
    {
        $context = $args[0];
        $params = $args[2];

        if(isset($params['contactFormEmail'])) {
            $context->setData( 'contactFormEmail', $params['contactFormEmail'] );
        }

    }

    // Add the field data to be rendered by VueJS
    public function contextSettings( $hookName, &$args )
    {
        $config = &$args[0];
        if($config['id'] == 'masthead') {

            $context = $this->getRequest()->getContext();

            $value = $context->getData('contactFormEmail') ?? '';

            $config['fields'][] = [
                'name' => 'contactFormEmail',
                'component' => 'field-text',
                'label' => 'Contact Form Email',
                'groupId' => 'identity',
                'value' => $value,
                'inputType' => 'text',
            ];

        }
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
