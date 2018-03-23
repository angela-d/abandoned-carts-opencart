### If you are submitting code changes ###
Submit a [pull request](../../pulls) - I will not merge code from issues attachments!

### If you are submitting a language  ###
Either issues attachments or pull requests will suffice.  (Pull requests are preferred, though)

**Please** ensure you do not skip the language references on [install.xml](install.xml)

Please mention in your comments how you would like your contributions credited (a link to your Github/pseudonym, etc)

### Pull-requests: Any kind of changes will require the versioning of install.xml to be bumped up ###
There are two references to version numbers in this xml file; which are highly important, as forgetting to update these entries will prevent users from upgrading their existing extension.

Snippets from [install.xml](install.xml) that will need to be changed:
```xml
<code>abandoned_carts_ocv102</code>
```
and
```xml
<version>1.0.2</version>
```
The version numbers are an assessment of changes from the previous version to the new version.  As a general rule of thumb:

| Type of Change  | Version Increment | Previous Version | New Version |
| ------------- | ------------- | ------------- | ------------- |
| Language addition  | x.x.1  | 1.0 | 1.0.1 |
| Minor Bugfix (few lines of code)  | x.x.1  | 1.0 | 1.0.1 |
| Significant Bugfix or change/additions  | x.1.0  | 1.0 | 1.1.0 |
| Substantial changes or additions  | 1.x.x | 1.0 | 2.0 |
