module.exports = {
  '@tags': ['regression'],

  before: function (browser) {
    // Resize default window size.
    browser.resizeWindow(1600, 2048);
    browser.drupalLogout();
  },

  '[Regression] D8NID-845: Ensure that anonymous users can view taxonomy canonical page content': browser => {

    // View the 'help' term page. It should show both a menu link item and not show 'Page not found'.
    browser.drupalRelativeURL('/taxonomy/term/402');

    const termName = 'Help';

    browser.expect.element('nav ul.nav-menu > li:nth-child(4)').text.to.equal(termName);
    browser.expect.element('h1.page-title').text.to.equal(termName);

  }

};
