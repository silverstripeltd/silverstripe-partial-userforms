<input type="hidden" id="$Name" name="$Name" value="$Value">
<button class="btn btn-add-more" $getAttributesHTML("class", "id", "name")>
    <% if $RepeatLabel %>
        $RepeatLabel
    <% else_if $RightTitle %>
        $RightTitle
    <% else %>
        Repeat
    <% end_if %>
</button>