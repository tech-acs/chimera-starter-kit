---
---

# Creating reports
Reports are compiled tabular datasets presented as CSV or Excel file formats. They are automatically generated based on a set schedule and can also be automatically emailed to designated users of the dashboard.

Just like indicators, reports can be organized into different pages. You can assign a report to appear on one or more pages. This is achieved during the edit process.

There are two ways to create report. A cli command and a web form.

The first way is by running the `php artisan chimera:make-report` command and following the various prompts. This works best when you are running a linux machine.

The second way is by going to the Manage dashboard menu and selecting Reports, then pressing the CREATE NEW button and filling out the form as directed.

## Implementing reports
Obviously, you will have to write some code in your generated report file so that it queries and returns the data that needs to be present in the generated report file.

You need to implement the getData() method and make sure it returns a Collection. The keys of the collection will become the column headers of the report spreadsheet and the values will become the rows.