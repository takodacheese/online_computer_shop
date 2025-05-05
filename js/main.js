$(document).ready(function() {
    // Form validation
    $('.form-submit').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        // Basic validation
        let isValid = true;
        form.find('input, select, textarea').each(function() {
            if ($(this).prop('required') && !$(this).val()) {
                isValid = false;
                $(this).addClass('error');
            }
        });

        if (isValid) {
            form.submit();
        }
    });

    // Cart functionality
    $('.add-to-cart').on('click', function() {
        const productId = $(this).data('product-id');
        const quantity = $(this).closest('.product').find('.quantity').val() || 1;
        
        $.ajax({
            url: 'mem_order/add_to_cart.php',
            type: 'POST',
            data: {
                product_id: productId,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    updateCartCount();
                    showNotification('Product added to cart!');
                } else {
                    showNotification(response.message, 'error');
                }
            }
        });
    });

    // Update cart count
    function updateCartCount() {
        $.get('mem_order/get_cart_count.php', function(data) {
            $('.cart-count').text(data.count);
        });
    }

    // Show notification
    function showNotification(message, type = 'success') {
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        notification.fadeIn().delay(3000).fadeOut(function() {
            $(this).remove();
        });
    }

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Handle image uploads
    $('.image-upload').on('change', function() {
        const file = this.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            showNotification('File size must be less than 5MB', 'error');
            this.value = '';
            return;
        }

        if (!file.type.match('image.*')) {
            showNotification('Please upload an image file', 'error');
            this.value = '';
            return;
        }

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            $(this).closest('.upload-container').find('.preview').attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });
});
