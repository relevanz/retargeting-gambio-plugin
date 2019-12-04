{extends file="get_usermod:layouts/main/layout.html"}
{block name="content"}
    {load_language_text section="relevatracking"}
    <iframe id="relevanz-stats" class="loading" src="{$content.statsFrame}"></iframe>
    <script type="text/javascript">{literal}
        document.addEventListener("DOMContentLoaded", function(event) {
            var wrapheight = $(document).height(),
                $iframe = $("#relevanz-stats");
            $iframe.on('load', function () {
                setTimeout(function () {
                    $iframe.removeClass('loading');
                }, 1000);
            });
            $iframe.attr('height', wrapheight);
        });
    {/literal}</script>
{/block}
