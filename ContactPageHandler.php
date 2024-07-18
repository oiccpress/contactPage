<?php

namespace APP\plugins\generic\contactPage;

use APP\core\Request;
use APP\handler\Handler;
use APP\plugins\generic\contactPage\ContactPagePlugin;
use APP\template\TemplateManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PKP\controllers\page\PageHandler;
use PKP\security\authorization\ContextAccessPolicy;
use PKP\security\authorization\ContextRequiredPolicy;
use PKP\security\authorization\PKPSiteAccessPolicy;

class ContactPageHandler extends Handler {

    public ContactPagePlugin $plugin;

    public function __construct(ContactPagePlugin $plugin)
    {
        parent::__construct();

        $this->plugin = $plugin;
    }

    public function index($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);

        $context = $request->getContext();

        if (!$request->checkCSRF()) {
            die("Invalid Request E:294");
        }

        if(strlen($_POST['telephone'])) {
            die("Invalid Request E:133");
        }

        $emlBody = ['<table>'];
        foreach([ 'name', 'email', 'subject', 'message' ] as $el) {
            if(empty($_POST[$el])) {
                die("Invalid Request E:293");
            }
            $emlBody[] = '<tr><th>' . ucwords($el) . '</th><td>' . htmlspecialchars($_POST[$el]) . '</td></tr>';
        }
        $emlBody[] = '</table><p>Email sent from OJS in an automated fashion</p>';

        $eml = $context->getData('contactFormEmail') ?? $context->getContactEmail();
        $mailable = new ContactMailable([
            'context' => $context
        ]);
        $mailable->from( $eml, 'Journal Admin' )->to( $eml )
            ->subject('Contact Form Submission')
            ->body( implode("\n",$emlBody) );
        
        Mail::send($mailable);

        return $templateMgr->display(
            $this->plugin->getTemplateResource(
                'contactFormSubmitted.tpl'
            )
        );
    }

}