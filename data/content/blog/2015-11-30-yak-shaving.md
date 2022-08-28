---
layout: post
title: "Yak Shaving Is the Entire Job Description"
date: 2015-11-30 00:00:00 +00:00
shorturl: http://bram.se/shaving-yaks
excerpt: >
    A recent struggle to solve a programming problem reminded me that yak shaving isn't just part of our jobs, it's the entire job description.
keywords: [job, enjoyment, job satisfaction, yak shaving, programming]
homepage:
    teaser: "Yak shaving isn't just part of our jobs, it's the entire job description."
    image: https://files.benramsey.com/ws/blog/2015-11-30-yak-shaving/homepage-500x667.jpg
image:
    url: https://files.benramsey.com/ws/blog/2015-11-30-yak-shaving/banner-1500x630.jpg
    alt: 'Yaks on the Tibetan plain'
    citation: '"Tibetan plain" by Jeroen Pots, licensed under CC BY-NC-ND 2.0'
    credit: Jeroen Pots
    link: https://www.flickr.com/photos/pixies4ever/5976402867/
categories: blog
tags: [job]
---
Earlier this year, I worked on a solution to help us manage changes and history when maintaining different versions of Amazon Machine Images (AMIs). I entertained a wide range of ideas from [Docker](https://www.docker.com/) to [AWS CloudFormation](https://aws.amazon.com/cloudformation/) to a collection of shell scripts.

Finally, after asking in #pynash (Nashville's Python user group) on Freenode IRC, [Jason Myers](http://jasonamyers.com/) pointed me to [Packer](https://packer.io/) as a potential solution.

> Packer is a tool for creating machine and container images for multiple platforms from a single source configuration.

Packer turned out to be the right tool for the job, but I almost scrapped it, since I ran into a few problems.

When I began my journey as a programmer, every task was fraught with problems, and I loved it. Everything was new, and every problem was an opportunity to learn and grow. It was great.

Somewhere along the way, though, problems became nuisances. As I grew older in life and my career, my tolerance for problems became lower, and my desire for things to Just Work™ became greater.

As I struggled to find a solution for the problem I had with Packer, [Tate Eskew](http://www.tateeskew.com/) reminded me that [yak shaving](https://en.wiktionary.org/wiki/yak_shaving) is a part of my job.

``` irc
<teskew> i'd just patch it, then. keep it in a central place so you only compile it once.
         then when it's put into mainline, just switch out the binary
<teskew> either way, it's a pretty simple patch to test and change
<ramsey> ugh… gotta set up an environment just to build packer
<ramsey> "simple"
<teskew> you act like yak-shaving isn't the name of the game
<ramsey> :P
<teskew> hell, it's the entire job description
<teskew> :)
<ramsey> I am not an ops person
<teskew> you are right now :)
```

I ended up [patching Packer](https://github.com/ramsey/packer/commit/016992bb5dcfdf9a62336ac135725cf0c1487ee4) for my needs, and I had fun doing it. I learned a valuable lesson that day: Despite how we may gripe and complain about shaving yaks, it's part of our job as programmers and problem solvers, and often, it's the part that brings us the most satisfaction. I've learned to embrace yak shaving, and doing so has changed my outlook on my job, open source contributions, and community organizing.

Yak shaving isn't just part of our jobs, it's the entire job description.
