Cypress.Commands.add("login", (username) => {
    cy.visit('/wp-login.php')
    cy.get('#user_login').wait(250).type(username)
    cy.get('#user_pass').wait(250).type('password')
    cy.get('#loginform').submit()
    cy.url().should('not.match', /wp-login/)
})

Cypress.Commands.add("logout", () => {
    cy.get('a[href^="http://localhost:8000/wp-login.php?action=logout"]').first().click({force: true})  // logout confirmation button
})

Cypress.Commands.add("checkout", () => {
    cy.visit('/product/product/')
    cy.get('.single_add_to_cart_button').click()
    cy.visit('/cart/')
    cy.get('.checkout-button').click()
})
