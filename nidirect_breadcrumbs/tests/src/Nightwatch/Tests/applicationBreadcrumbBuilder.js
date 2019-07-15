var parser = require('xml2json');
var http = require('http');
var nid, node;

module.exports = {
  '@tags': ['nidirect-breadcrumbs'],

  before: function (browser) {
    // Application.
    http.get(process.env.TEST_D7_URL + '/migrate/application', (response) => {
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

  'Test that Application node shows correct breadcrumb pattern': browser => {
    browser
      .pause(2000, function () {
        browser.drupalRelativeURL('/node/' + nid).waitForElementVisible('body', 1000);

        console.log('Evaluating output for node id (' + nid + '): ' + node.title);

        let subthemesHierarchy = node.subtheme_hierarchy.split('›');

        console.log('Migration node endpoint provided these subtheme terms: ' + subthemesHierarchy.toString());

        for (let i = 0; i < subthemesHierarchy.length; i++) {
          browser.expect.element('nav.breadcrumb').text.to.contain(subthemesHierarchy[i]);
        }

      })
  }

};
