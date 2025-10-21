{*
  plugins/AjaxSearch/frontend/templates/searchbar.tpl
*}
<div id="cc-ajax-search" class="cc-ajax-search" data-t-empty="{__("cc.search.empty")}" data-t-error="{__("cc.search.error")}">
  <input type="search"
         class="cc-ajax-search__input"
         placeholder="{__("cc.search.placeholder")}"
         aria-label="{__("cc.search.aria")}"
         autocomplete="off" />
  <div class="cc-ajax-search__dropdown" role="listbox" aria-hidden="true"></div>
</div>

<link rel="stylesheet" href="{$ShopURL}/plugins/AjaxSearch/frontend/css/search.css" />
<script defer src="{$ShopURL}/plugins/AjaxSearch/frontend/js/search.js"></script>
