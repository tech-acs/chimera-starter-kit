---
---

# Introduction


## Origin story
CSPro is the most widely used census and survey tool in most African countries. It is the choice of many exactly because it allows
for a lot of flexibility in how to prepare questionnaires and also on how data gets synced back from the field.

However, during field work, it stores the received data in a format that is not easily accessible and queryable. If you are desirous of 
gaining any insight into the data that is being collected, there are but a few ways of going about it.

Our original attempt to solve this problem was by "breaking out" the data in to a relational database format using a tool we built called [pyCSPro](https://pypi.org/project/pycspro/). Later on, CSPro added a similar tool into their CSWeb (the field data receiving server part of CSPro) suite and 
made a relational database version of the data available.

While earlier versions of our dashboards were specifically built and were dependent on the breakout database, current versions can be used with almost any kind of modern data source.

## Philosophy
Our guiding philosophy in shaping this tool is to make it as painless and straightforward as possible to create dashboards for field exercises such as censuses and surveys.

We worry about all the edge cases and details so that you do not have to. You can simply focus on what indicators you desire to have and the kit will take care of the rest.

## Uses
We envision the Dashboard Starter Kit being used in all sorts of scenarios where one needs to gain insight into some data. These would usually be key performance indexes, and other indicators which allow you to monitor the quality of the data.