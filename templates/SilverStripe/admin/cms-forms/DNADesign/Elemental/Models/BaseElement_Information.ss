<%-- Simple template for BaseElement_Information in admin theme --%>
<div class="base-element-information">
    <% if $Title %>
        <h3>$Title</h3>
    <% end_if %>
    
    <% if $Type %>
        <p><strong>Type:</strong> $Type</p>
    <% end_if %>
    
    <% if $Summary %>
        <p><strong>Summary:</strong> $Summary</p>
    <% end_if %>
</div> 