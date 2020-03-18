{load_language_text section="relevanz"}
{load_language_text section="admin_buttons" name="admin_buttons"}

{extends file="get_usermod:layouts/main/layout.html"}
{block name="content"}
    <div class="configform-wrapper">
        {foreach $content.messages as $message}
        <div class="alert alert-{$message.type}">
            {*<span>{$txt['msg_'|cat:$message.code]}</span>*}
            <span>{$message.msg}</span>
        </div>
        {/foreach}

        <form id="relevanz-configuration-form" class="form-horizontal relevanz-configuration-form"
              action="{$content.action}" method="post" enctype="multipart/form-data">
            <fieldset>
                <legend>{$txt.legend_credentials}</legend>

                {assign var='helpBox' value={$txt.label_apikey_tooltip}}
                {assign var='helpBox' value={$helpBox|replace:['[br]','[plink]','[/plink]']:['<br>','<a style="text-decoration: underline;" href="https://releva.nz" target="_blank">','</a>']}}

                <div class="form-group visibility_switcher">
                    <label for="export_url" class="col-md-3">
                        {$txt.label_apikey}
                    </label>
                    <div class="col-md-7">
                        <input type="text" id="conf_apikey" name="conf[apikey]" class="form-control" value="{$content.credentials->getApiKey()}" required />
                    </div>
                    <div class="col-md-2">
                        <span class="tooltip-icon" data-gx-widget="tooltip_icon" data-tooltip_icon-type="info">{$helpBox|unescape:"html" nofilter}</span>
                    </div>
                </div>

                {if $content.credentials->isComplete()}
                <div class="form-group visibility_switcher">
                    <label class="col-md-3">{$txt.label_customerid}</label>
                    <div class="col-md-7">
                        <input type="text" class="form-control" value="{$content.credentials->getUserId()}" readonly="">
                    </div>
                    <div class="col-md-2"></div>
                </div>

                <div class="form-group visibility_switcher">
                    <label for="export_url" class="col-md-3">
                        {$txt.label_exporturl}
                    </label>
                    <div class="col-md-7">
                        <input type="text" id="export_url" name="scheme[export_url]" class="form-control" value="{$content.urlExport}" readonly="">
                    </div>
                    <div class="col-md-2">
                        <span class="tooltip-icon" data-gx-widget="tooltip_icon" data-tooltip_icon-type="info">{$txt.label_exporturl_tooltip}</span>
                    </div>
                </div>
                {/if}
            </fieldset>

            {if $content.ccCategories != null}
            <fieldset>
                <legend>{$txt.legend_cookieconsent}</legend>
                <div class="form-group visibility_switcher">
                    <label class="col-md-3">{$txt.label_cookieconsentcategory}</label>
                    <div class="col-md-7">
                        <select id="conf_ccc" name="conf[ccc]" class="form-control">
                            {foreach $content.ccCategories as $ccid => $ccname}
                                <option value="{$ccid}" {if $ccid == $content.ccCurrentCategory} selected{/if}>{$ccname}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <span class="tooltip-icon" data-gx-widget="tooltip_icon" data-tooltip_icon-type="info">{$txt.label_cookieconsentcategory_tooltip}</span>
                    </div>
                </div>
            </fieldset>
            {/if}
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function(event) {
            $('input[type="text"][readonly]').click(function () {
                var t = $(this)[0];
                t.focus();
                t.select();
            });
        });
    </script>
{/block}

{block name="bottom_save_bar"}
    <button class="btn btn-primary"
            onclick="document.getElementById('relevanz-configuration-form').submit()">{$admin_buttons.BUTTON_SAVE}
    </button>
{/block}
