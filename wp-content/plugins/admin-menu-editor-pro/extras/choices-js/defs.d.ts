//For PhpStorm's benefit, let it know about Choices.js.
declare const Choices: typeof import('choices.js').default;

//I also tried using a '/// <reference types="choices.js" />' directive, but the IDE still
//complained and wanted to add an import statement, which would require a build step.