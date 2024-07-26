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

use APP\core\Services;
use Illuminate\Support\Facades\DB;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;
use PKP\services\PKPSchemaService;

class ContactPagePlugin extends GenericPlugin {

    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path);

        if ($success && $this->getEnabled()) {
            Hook::add( 'BlockPages::blocks', [ $this, 'blockConfigs' ] );
            Hook::add( 'TemplateResource::getFilename', [$this, '_overridePluginTemplates'] );
            Hook::add( 'LoadHandler', [$this, 'setPageHandler'] );

            Hook::add( 'Form::config::after', [$this, 'contextSettings'] );
            Hook::add( 'Schema::get::context', [ $this, 'addToContextSchema' ] );
            Hook::add( 'Context::edit', [ $this, 'editContext' ] );

            Hook::add( 'Schema::get::site', [ $this, 'addToContextSchema' ] );
            Hook::add( 'Site::edit', [ $this, 'editContext' ] );

            Hook::add( 'ContactForm::subject', [ $this, 'subjectTemplate' ] );
        }

        return $success;
    
    }

    public function directGetSiteSetting(string $settingName)
    {
        $r = DB::selectOne('SELECT * FROM site_settings WHERE setting_name = ?', [ $settingName ]);
        if($r) {
            return $r->setting_value;
        } else {
            return null;
        }
    }

    public function subjectTemplate(string $hookName, array $args) : bool
    {

        $templateMgr = &$args[1];

        $context = $this->getRequest()->getContext();
        if($context) {
            $value = $context->getData('contactFormSubject');
        } else {
            $value = $this->directGetSiteSetting('contactFormSubject');
        }

        if($value) {
            $templateMgr->assign('subjects', explode("\n", $value));
        }

        return false;

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

        $schema->properties->{"contactFormSubject"} = (object)[
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

        $schemaService = Services::get('schema');
        $schemaService->get(PKPSchemaService::SCHEMA_SITE, true);

        if(isset($params['contactFormEmail'])) {
            $context->setData( 'contactFormEmail', $params['contactFormEmail'] );
        }

        if(isset($params['contactFormSubject'])) {
            $context->setData( 'contactFormSubject', $params['contactFormSubject'] );
        }

    }

    // Add the field data to be rendered by VueJS
    public function contextSettings( $hookName, &$args )
    {
        $config = &$args[0];
        if($config['id'] == 'masthead' || $config['id'] == 'siteConfig') {

            $context = $this->getRequest()->getContext();

            $value = $context ? $context->getData('contactFormEmail') : $this->directGetSiteSetting('contactFormEmail');

            $config['fields'][] = [
                'name' => 'contactFormEmail',
                'component' => 'field-text',
                'label' => 'Contact Form Email',
                'groupId' => $config['id'] === 'masthead' ? 'identity' : 'default',
                'value' => $value,
                'inputType' => 'text',
            ];

            $value = $context ? $context->getData('contactFormSubject') : $this->directGetSiteSetting('contactFormSubject');

            $config['fields'][] = [
                'name' => 'contactFormSubject',
                'component' => 'field-textarea',
                'label' => 'Contact Form Subjects',
                'groupId' => $config['id'] === 'masthead' ? 'identity' : 'default',
                'value' => $value,
                'inputType' => 'textarea',
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
