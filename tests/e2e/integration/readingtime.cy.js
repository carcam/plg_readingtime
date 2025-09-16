describe('Reading Time Plugin', () => {
  it('should display the reading time on an article', () => {
    cy.visit('/index.php/en/blog/welcome-to-joomla');
    cy.get('.rt-reading-time').should('be.visible');
  });
});
