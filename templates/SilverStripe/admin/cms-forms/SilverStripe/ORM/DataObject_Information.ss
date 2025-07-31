<%-- Simple template for DataObject_Information in admin theme --%>
<div class="data-object-information">
    <% if $Title %>
        <h3>$Title</h3>
    <% end_if %>
    
    <% if $ClassName %>
        <p><strong>Class:</strong> $ClassName</p>
    <% end_if %>
    
    <% if $ID %>
        <p><strong>ID:</strong> $ID</p>
    <% end_if %>
    
    <% if $Created %>
        <p><strong>Created:</strong> $Created.Nice</p>
    <% end_if %>
    
    <% if $LastEdited %>
        <p><strong>Last Edited:</strong> $LastEdited.Nice</p>
    <% end_if %>
</div> 