<div class="contact-form my-3">
    <form method="post" class="card" action="{url page="contactform"}">

        {csrf}

        <div class="card-body">
            <label class="d-block" for="name">
                {translate key="plugins.generic.contactForm.name"}
                <span class="text-danger required">{translate key="plugins.generic.contactForm.required"}</span>
            </label>
            <input class="form-control" type="text" name="name" id="name" required />

            <label class="d-block mt-3" for="email">
                {translate key="plugins.generic.contactForm.email"}
                <span class="text-danger required">{translate key="plugins.generic.contactForm.required"}</span>
            </label>
            <input class="form-control" type="email" name="email" id="email" required />

            {call_hook name="ContactForm::subject"}

            <label class="d-block mt-3" for="subject">
                {translate key="plugins.generic.contactForm.subject"}
                <span class="text-danger required">{translate key="plugins.generic.contactForm.required"}</span>
            </label>
            {if $subjects}
                <select class="form-control" name="subject" id="subject" required>
                    {foreach from=$subjects item=$subject}
                        <option>{$subject}</option>
                    {/foreach}
                </select>
            {else}
                <input class="form-control" type="text" name="subject" id="subject" required />
            {/if}


            <label class="d-block mt-3" for="message">
                {translate key="plugins.generic.contactForm.message"}
                <span class="text-danger required">{translate key="plugins.generic.contactForm.required"}</span>
            </label>
            <textarea class="form-control" name="message" id="message" required></textarea>

            <div class="d-none">
                <label class="mt-3" for="telephone">
                    Telephone
                    <span class="text-danger required">Telephone</span>
                </label>
                <input class="form-control" type="text" name="telephone" id="telephone" />
            </div>

            <br/>

            <button type="submit" class="btn btn-primary">{translate key="plugins.generic.contactForm.sendEnquiry"}</button>
        </div>
    </form>
</div>