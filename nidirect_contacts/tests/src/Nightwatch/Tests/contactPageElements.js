module.exports = {
  '@tags': ['nidirect-contacts'],

  'A-Z block is not present on search term results page': browser => {
    browser
      .drupalRelativeURL('/contacts?query_contacts_az=test')
      .expect.element('#contacts-az')
      .to.not.be.present;
  },

  'Search by relevancy link is not present on default search by term page': browser => {
    browser
      .drupalRelativeURL('/contacts?query_contacts_az=test')
      .expect.element('.view-contacts')
      .text.to.not.contain('Sort by relevancy');
  },

  'Contacts by letter does not use a pager': browser => {
    browser
      .drupalRelativeURL('/contacts/letter/a')
      .expect.element('.view-contacts-a-z nav.pager')
      .to.not.be.present;
  },

  'Contacts by letter confirms: "Showing entries for A"': browser => {
    browser
      .expect.element('.view-contacts-a-z .view-header')
      .text.to.contain('Showing entries for A');
  },

  'Contacts by letter displays Reset search convenience link': browser => {
    browser
      .expect.element('.view-contacts-a-z .view-links')
      .text.to.contain('Reset search');
  },

  'Contacts by letter displays Show A-Z convenience link': browser => {
    browser
      .expect.element('.view-contacts-a-z .view-links')
      .text.to.contain('Show A-Z');
  },

  'Contacts by letter does not show any sorting links': browser => {
    browser
      .expect.element('.view-links')
      .text.to.not.match(/Sort .+/);
  },

  'Contact landing page should not show any sorting or convenience links': browser => {
    browser
      .drupalRelativeURL('/contacts')
      .expect.element('.view-links')
      .to.not.be.present;
  },

  'Contact landing page should show the A-Z block': browser => {
    browser
      .drupalRelativeURL('/contacts')
      .expect.element('#contacts-az')
      .to.be.present;
  },

  'Contact landing page should show the A-Z block': browser => {
    browser
      .drupalRelativeURL('/contacts')
      .expect.element('#contacts-az')
      .to.be.present;
  },

};
