// Script to dynamically handle cart updates and other homepage interactivity

// Placeholder cart count - to be replaced with backend data integration
let cartCount = 0;

// Function to add items to cart
function addToCart(itemName, itemPrice) {
    alert(`${itemName} has been added to your cart.`);
    cartCount++;
    document.getElementById('cart-count').innerText = cartCount;
}


// Redirect user to login or register when trying to add to cart without logging in
function requireLogin() {
    alert("You need to log in or register to add items to your cart.");
    window.location.href = "login_user.html"; // Redirect to login page
}

// Initialize event listeners (if needed)
document.addEventListener('DOMContentLoaded', () => {
    // Example: Attach 'requireLogin' function to all cart buttons
    const cartButtons = document.querySelectorAll('.btn');
    cartButtons.forEach(button => {
        button.addEventListener('click', requireLogin);
    });
});
