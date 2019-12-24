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

    cy.login('customer')
    cy.checkout()
    cy.get('.woocommerce-checkout-payment').scrollIntoView().should('contain', 'Net 30')
  })
})
