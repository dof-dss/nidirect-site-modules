var parser = require('xml2json');
var http = require('http');
var nid, node;

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  before: function (browser) {
    // Contact/NIDirect contacts.
    http.get(process.env.TEST_D7_URL + '/migrate/nidcontact', (response) => {
      let data = '';
      response.on('data', (chunk) => { data += chunk });

      response.on('end', () => {
        data = JSON.parse(parser.toJson(data));
        node = data.nodes.node;
        nid = node.nid;
      })
    }).on("error", (err) => {
      console.log("Error: " + err.message);
    });
  },

  'Test that Contact node shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser
          .drupalRelativeURL('/node/' + nid)
          .waitForElementVisible('body', 1000)
          .expect.element('nav.breadcrumb')
          .to.have.text.to.match(/(Home)\W+(Contacts)$/);
      })
  }

};
