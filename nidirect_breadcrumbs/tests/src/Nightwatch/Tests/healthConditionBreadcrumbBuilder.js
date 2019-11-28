var parser = require('xml2json');
var http = require('http');
var nid, node;
const breadcrumbSegments = [
  'Home',
  'Health and wellbeing',
  'Illnesses and conditions',
  'A to Z',
];

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  before: function (browser) {
    // GP Practice nodes.
    http.get(process.env.TEST_D7_URL + '/migrate/healthcond', (response) => {
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

  'Test that Health condition node shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser
          .drupalRelativeURL('/node/' + nid)
          .waitForElementVisible('body', 2000);

        breadcrumbSegments.forEach(function (item) {
          browser.expect.element('nav.breadcrumb .breadcrumb--list').text.to.contain(item);
        });
      })
  }

};
