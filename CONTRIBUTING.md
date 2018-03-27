These are instructions for *you* to follow if you want to contribute; this extension is open source, so you are free to modify any part of it, so as long as your contribution is useful and fall within the suggested guidelines:

### If you are submitting code changes ###
Submit a [pull request](../../pulls) - I will not merge code from issues attachments!

### Submitting a language  ###
Either issues attachments or pull requests will suffice.  (Pull requests are preferred, though)

**Please** ensure you do not skip the language references on [install.xml](install.xml)

Please mention in your comments how you would like your contributions credited (a link to your Github/pseudonym, etc)

### Pull-requests: Any kind of changes will require the versioning of install.xml to be incremented ###
There are two references to version numbers in this xml file; which are highly important, as forgetting to update these entries will prevent users from upgrading their existing extension.

Snippets from [install.xml](install.xml) that will need to be changed:
```xml
<code>abandoned_carts_ocv102</code>
```
and
```xml
<version>1.0.2</version>
```
The version numbers are an assessment of changes from the previous version to the new version.  As a general rule of thumb, follow this example chart to know how many numbers to increment:

| Type of Change  | Version Increment | Previous Version | New Version |
| ------------- | ------------- | ------------- | ------------- |
| Language addition  | x.x.1  | 1.0 | 1.0.1 |
| Minor Bugfix (few lines of code)  | x.x.1  | 1.0 | 1.0.1 |
| Significant Bugfix or change/additions  | x.1.0  | 1.0 | 1.1.0 |
| Substantial changes or additions  | 1.x.x | 1.0 | 2.0 |

Don't forget to re-package the /upload/ directory and install.xml into a .zip

### How to Make a Pull Request ###

* [Fork this repository](../../../abandoned-carts-opencart) (the fork button is usually just beneath your avatar)
* This will copy the repository to your Github account. You can then navigate through the source code & make changes.
* Once you are ready to submit ALL of your changes (please do not send a single PR per file change; send them all in one swoop - Github will keep track of everything for you), [submit a pull request](https://help.github.com/articles/creating-a-pull-request-from-a-fork/) - this button is typically available on the main page of *your* repository

Pull requests are preferred because your contribution can be automatically merged into the codebase without affecting the code around it.

For new languages, zip attachments are fine because nothing will be affected by its introduction; however, if a language already exists, then it becomes difficult to update it without merging.
