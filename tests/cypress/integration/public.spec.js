describe('Public', function () {
  it('Adds to cart', function () {
    cy.visit('/product/product/')
    cy.get('.single_add_to_cart_button').click()
    cy.visit('/cart/')
    cy.get('.checkout-button').click()
    cy.get('.woocommerce-checkout-payment').scrollIntoView().should('contain', 'no available payment methods')
  })
})
