### Learn PHP The Right Way Course Project
This repository contains the source code of the project from the fourth/project section of the [Learn PHP The Right Way](https://youtube.com/playlist?list=PLr3d3QYzkw2xabQRUpcZ_IBk9W50M9pe-) series from YouTube. 

### Branches
The **main** branch will always contain the latest or most up-to-date code for the project. If you want to just work with the finished project/code then stick with the main branch. Each lesson will also have dedicated branches: **PX_Start** & **PX_End**. **X** in here indicates the lesson number & **P** indicates the section. You will find lesson numbers in the thumbnail of the videos. The **Start** & **End** simply indicate the starting code at the beginning of the video & the ending code by the end of the video. Note that some videos may not contain the **PX_End** if the code was not changed. If you want to follow along the video & code along with me then pick the branch appropriate for the lesson that you are watching & make sure it's the one with **_Start**. If you just want to see the solution or the final code for that lesson then you can use the branch appropriate for the lesson with **_End** (if applicable).

Here are some examples:

* If you are watching lesson **P1** and want to code along, then use the branch **P1_Start**
* If you are watching lesson **P3** and just want to see the end result of that lesson, then use the branch **P3_End** if it exists, if not then use the **P3_Start**
* If you just want to see the up-to-date source code of the project as we build it or the final project code once we've finished building it then stick to the **main** branch

### Tips
* Make sure to run `composer install` & `npm install` after you pull the latest changes or switch to a new branch so that you are always using the same versions of dependencies that I do during the lessons
* Run `npm run dev` if you want to build assets for development
* Run `npm run build` if you want to build assets for productions
* Run `npm run watch` if you want to build assets during development & have it automatically be watched so that it rebuilds after you make updates to front-end
* Run `docker-compose up -d --build` to rebuild docker containers if you are using docker to make sure you are using the same versions as the videos
