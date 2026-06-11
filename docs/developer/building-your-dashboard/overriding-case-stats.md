---
---

# Overriding case stats
CaseStats are high level statistics about the number and type of cases/interviews you have in your database. They can be classified as *completed, partial and duplicate*.

This classification is bound up with how cases are stored in CSPro and might not necessarily be the way you want to summarize the interviews in your database. 

You can override this default, CSPro based, implementation and provide your own kind of CaseStats. To do so, just create a new Livewire component and make sure you include the words CaseStats in the name.

You then need to extend the existing CaseStats class and then override either just the getData() method, where you can implement your own counting strategy or also override the render() method to also provide your own blade view to render.

Once you have implemented your new CaseStats component, it should become available for selection under the Source edit form for each data source/questionnaire.

When overriding either of these two methods, please make sure you return the results in the expected format. You can refer to the existing CaseStats component for that.
