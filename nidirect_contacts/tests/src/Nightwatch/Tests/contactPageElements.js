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
  }

};
