<%-- Simple template for GridFieldAddNewMultiClass to prevent template errors --%>
<% if $Classes %>
    <div class="grid-field-add-new-multi-class">
        <% loop $Classes %>
            <button type="button" class="btn btn-primary" data-class="$Key">
                $Value
            </button>
        <% end_loop %>
    </div>
<% end_if %> 