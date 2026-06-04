I want to add an MCP server to this package.

Its main purpose would be to help coding agents such as opencode, clause claude, etc. to create various types of dashboard artefacts such as indicators, scorecards, gauges, reports etc.

If we take one type of artefact, at the moment, a user goes through the following steps to create it:

1. Create the artefact using either its make command or by using the "create new" button on the manage page of that artefact type. This will create the component file from a stub.
2. Edit the component file to implement the getData() function so that it returns the data for the artefact.
3. With some components, implementing the getData() function is all you need to do as their visual aspects are fixed. But with some artefacts such as indicators, there is a third step where the user will use a visual editor (plotly-chart-editor) to create the chart.

So, artefacts live in two places: database and the filesystem.

I will be using laravel/mcp to create the MCP server.

Implementing the getData() methods will require knowledge of how to use the BreakoutQueryBuilder and also what the structure of the source database is.
The source database can be undestood by reading the dictionary file that was used to create it (CSPro).

And for indicators, knowledge of Plotly is required (https://context7.com/plotly/graphing-library-docs)
