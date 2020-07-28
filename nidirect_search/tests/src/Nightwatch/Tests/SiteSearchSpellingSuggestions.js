module.exports = {
  '@tags': ['search'],

  'Spelling suggestions for site search': browser => {

    browser
      .drupalRelativeURL('/search?query=covod')
      .expect.element('.sapi-did-you-mean').text.to.contain('Did you mean');
    browser.expect.element('.sapi-did-you-mean > a').text.to.contain('covid');

    browser
      .drupalRelativeURL('/search?query=benfit')
      .expect.element('.sapi-did-you-mean').text.to.contain('Did you mean');
    browser.expect.element('.sapi-did-you-mean > a').text.to.contain('benefit');

    browser
      .drupalRelativeURL('/search?query=medcal')
      .expect.element('.sapi-did-you-mean').text.to.contain('Did you mean');
    browser.expect.element('.sapi-did-you-mean > a').text.to.contain('medical');

    browser
      .drupalRelativeURL('/search?query=belfst')
      .expect.element('.sapi-did-you-mean').text.to.contain('Did you mean');
    browser.expect.element('.sapi-did-you-mean > a').text.to.contain('belfast');

  },

  'Confirm search results on: site search, contacts search, GP search, & health conditions search pages': browser => {

    // Site search.
    browser
      .drupalRelativeURL('/search?query=nidirect')
      .expect.element('h2.view--count').to.be.present;

    // Contacts search.
    browser
      .drupalRelativeURL('/contacts?query_contacts_az=services')
      .expect.element('h2.view--count').to.be.present;

    // GP search.
    browser
      .drupalRelativeURL('/services/gp-practices?search_api_views_fulltext=surgery')
      .expect.element('h2.view--count').to.be.present;

    // Health conditions: search term (A-Z uses db search).
    browser
      .drupalRelativeURL('/services/health-conditions-a-z?query_health_az=cough')
      .expect.element('.view-display-id-search_page .view-empty').not.to.be.present;

  }

};
