module.exports = {
  '@tags': ['search'],

  'Spelling suggestions for contacts search': browser => {

    // Resize default window size.
    browser.resizeWindow(1600, 1024);

    browser
      .waitForElementVisible('body', 1000)
      .drupalRelativeURL('/contacts?query_contacts_az=marne')
      .expect.element('.sapi-did-you-mean').text.to.contain('Did you mean');
    browser.expect.element('.sapi-did-you-mean > a').text.to.contain('marine');

  },

  'Confirm search results on: contacts search page': browser => {

    // Resize default window size.
    browser.resizeWindow(1600, 1024);

    // Contacts search.
    browser
      .drupalRelativeURL('/contacts?query_contacts_az=services')
      .waitForElementVisible('body', 1000)
      .expect.element('h2.view--count').to.be.present;

  },

};
