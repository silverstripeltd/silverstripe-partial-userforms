<fieldset class="repeat-fieldset">
    <% if $Message %><span class="message $MessageType">$Message</span><% end_if %>
    <div class="middleColumn repeat-source">
        <% loop $FieldList %>
            $FieldHolder
        <% end_loop %>
    </div>
	<div class="middleColumn repeat-destination"></div>
	<div class="middleColumn repeat-button<% if $extraClass %> $extraClass<% end_if %>">
        <% if $Title %><legend class="left">$Title</legend><% end_if %>
        $Field
	</div>
</fieldset>
