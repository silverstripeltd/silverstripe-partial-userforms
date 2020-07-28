<fieldset class="repeat-fieldset">
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <div class="middleColumn repeat-destination"></div>
    <div class="middleColumn repeat-source" style="display: none">
        <% loop $FieldList %>
            $FieldHolder
        <% end_loop %>
    </div>
    <div class="middleColumn repeat-button<% if $extraClass %> $extraClass<% end_if %>">
        <% if $RightTitle %><legend class="left">$RightTitle</legend><% end_if %>
        $Field
    </div>
</fieldset>
