<% with $Cart %>
    <% if $cartEmpty %>
        <div>No items in cart yet</div>
        <a href='$Top.getEcommerceRootPageLink'><button>Go Shopping</button></a>
    <% else %>
        <% include ShowCart CartLink=$Top.getCartLink,CurrencySymbol=$Top.CurrentCurrencySymbol %>
    <a href='$Top.getCartLink/clearCart'><button>Clear Cart</button></a>
    <a href="$Top.getEcommerceRootPageLink/checkout"><button>Checkout</button></a>
    <% end_if %>
<% end_with %>