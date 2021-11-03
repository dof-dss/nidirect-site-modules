module.exports = {
  '@tags': ['regression', 'debug'],

  before: function (browser) {
    // Resize default window size.
    browser.resizeWindow(1600, 2048);
  },

  '[Regression] D8NID-1388: Ensure that taxonomy lists are purged when a landing page theme/subtheme value changes': browser => {

    // Login as an editor.
    browser
      .drupalLogin({ name: process.env.NW_TEST_USER_PREFIX + '_editor', password: process.env.TEST_PASS })
      .drupalRelativeURL('/node/add/landing_page');

    // Create a new landing page, take over Environment and the outdoors theme (field_subtheme)
    // and also appear as a subtheme under 'Travel transport and roads' (field_site_themes).
    browser
      .setValue('input#edit-title-0-value', ['A new landing page [regression test for D8NID-1388]', browser.Keys.TAB])
      .pause(2000)
      .setValue('input#edit-field-teaser-0-value', 'D8NID-1388.')
      .click('select[id="edit-field-subtheme-shs-0-0"]')
      .click('select[id="edit-field-subtheme-shs-0-0"] option[value="26"]')
      .click('select[id="edit-field-site-themes-shs-0-0"]')
      .click('select[id="edit-field-site-themes-shs-0-0"] option[value="15"]')
      .click('select[id="edit-moderation-state-0-state"] option[value="published"]')
      .click('input#edit-submit');

    // Go to theme page - the landing page should take over and no taxonomy list should show.
    browser.drupalRelativeURL('/')
      .click('xpath', '//*[@id="main-content"]//a/h3[normalize-space()="Environment and the outdoors"]')
      .expect.element('.list-content ul').not.to.be.present;

    // Go to the subtheme page - the node should appear in the list.
    browser.drupalRelativeURL('/')
      .click('xpath', '//*[@id="main-content"]//a/h3[normalize-space()="Travel, transport and roads"]')
      .expect.element('.list-content ul').to.be.present;

  }

};
