describe('Customer', function () {
  it('Net terms disabled', function () {
    cy.login('admin')
    cy.visit('/wp-admin/user-edit.php?user_id=2')
    cy.get('#net_terms_enabled').scrollIntoView().uncheck()
    cy.get('#net_terms_days').clear().type('30')
    cy.get('#submit').click()
    cy.get('body').should('contain', 'User updated')
    cy.logout()

    cy.login('customer')
    cy.checkout()
    cy.get('.woocommerce-checkout-payment').scrollIntoView().should('contain', 'no available payment methods')
  })
  it('Net terms enabled', function () {
    cy.login('admin')
    cy.visit('/wp-admin/user-edit.php?user_id=2')
    cy.get('#net_terms_enabled').scrollIntoView().check()
    cy.get('#net_terms_days').clear().type('30')
    cy.get('#submit').click()
    cy.get('body').should('contain', 'User updated')
    cy.logout()

    // Make sure net 30 is an option
    cy.login('customer')
    cy.checkout()
    cy.get('.woocommerce-checkout-payment').scrollIntoView().should('contain', 'Net 30')

    // Checkout
    cy.get("#billing_first_name").clear().type("Example")
    cy.get("#billing_last_name").clear().type("User")
    cy.get("#billing_address_1").clear().type("Example street")
    cy.get("#billing_country").select("US", {force: true})
    cy.get("#billing_state").select("KS", {force: true})
    cy.get("#billing_city").clear().type("Example city")
    cy.get("#billing_postcode").clear().type("67117")
    cy.get("#billing_phone").clear().type("000000")
    cy.get("form.woocommerce-checkout").submit()
    cy.url().should('include', 'order-received')

    // Make sure instructions are shown
    // TODO make sure only 1 instance, not 2 (double hooks)
    cy.get(".woocommerce-thankyou-order-details").wait(250).scrollIntoView().should('contain', 'Please send payment within 30 days.')
  })
})
