<input type="hidden" id="$Name" name="$Name" value="$Value">
<button class="btn btn-add-more" $getAttributesHTML("class", "id", "name")>
    <% if $RightTitle %>$RightTitle<% else %>Add more<% end_if %>
</button>