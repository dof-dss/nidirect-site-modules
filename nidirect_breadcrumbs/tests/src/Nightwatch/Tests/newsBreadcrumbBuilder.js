var parser = require('xml2json');
var http = require('http');
var nid, node;
const breadcrumbSegments = [
  'Home',
  'News',
];

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  before: function (browser) {
    // News nodes.
    http.get(process.env.TEST_D7_URL + '/migrate/news', (response) => {
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

  'Test that News node shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser
          .drupalRelativeURL('/node/' + nid)
          .waitForElementVisible('body', 2000)
          .expect.element('nav.breadcrumb .breadcrumb--list').to.have.text.to.match(/(Home)\W+(News)$/);
      })
  }

};
