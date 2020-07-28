<input type="hidden" id="$Name" name="$Name" value="$Value">
<button class="btn-add-more<% if $extraClass %> $extraClass<% end_if %>" $getAttributesHTML("class", "id", "name")>
    <% if $RepeatLabel %>
        $RepeatLabel
    <% else %>
        &#43;
    <% end_if %>
</button>