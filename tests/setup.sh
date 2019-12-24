#!/bin/sh -ex
# wp --allow-root core install --url=http://localhost:8000 --title=Testing --admin_user=admin --admin_email=admin@example.com
wp --allow-root user update admin --user_pass=password

wp --allow-root rewrite structure '/%postname%/'

wp --allow-root plugin install woocommerce --activate  # version
wp --allow-root theme install storefront --activate

wp --allow-root post create --post_content=[woocommerce_cart] --post_title=Cart --post_type=page --post_status=publish
wp --allow-root option update woocommerce_cart_page_id 5
wp --allow-root post create --post_content=[woocommerce_checkout] --post_title=Checkout --post_type=page --post_status=publish
wp --allow-root option update woocommerce_checkout_page_id 6
wp --allow-root post create --post_content=[woocommerce_my_account] --post_title="My Account" --post_type=page --post_status=publish
wp --allow-root option update woocommerce_myaccount_page_id 7

wp --allow-root wc product create --user=admin --name=Product --slug=product --regular_price=1000
wp --allow-root user create customer customer@example.com --role=customer --user_pass=password

wp --allow-root plugin activate woocommerce-net-terms

wp --allow-root wc --user=admin payment_gateway update net_terms --enabled=true
