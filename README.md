# phpstan-dhexpendency-rules
A set of additional rules for  [phpstan](https://github.com/phpstan/phpstan). The intention of this rules is to foment to respect the dependency flow in a hexagonal architecture. 

These rules assume that in the typical hexagonal architecture there are 3 layers: **Infrastructure**, **Application** and **Domain**.

There is a flow within these layers, this defines how the dependencies should interact to each other according the layer they are located in, and the flow is:
**Infrastructure** ➡️ **Application** ➡️ **Domain**. 

The interpretation of this flow is:
- The **Infrastructure** layer can "know" or communicate with the **Application** and **Domain** layers
- The **Application** layer can "known" or communicate ONLY with the **Domain** layer
- The **Domain** layer can ONLY communicate with itself

This ruleset attempts to help verifying the dependency flow is being applied properly in your project.

## Installation

Run

```shell 
composer require --dev cooldevguys/phpstan-dhexpendency-rules
```
If you use PHPStan extension installer, you're all set. If not, you need to manually register all the rules in your `phpstan.neon` file:

```yaml
includes:
  - vendor/cooldevguys/phpstan-dhexpendency-rules/rules.neon
```
## Configuration
You need to add your own values as parameters to your phpstan.neon:

```yaml
parameters:
  myVendorName: CoolDevGuys
  vendorStrictMode: true
  ignoredExternalVendors: ['IgnoredVendor']
  infrastructureLayerName: Infra
  applicationLayerName: App
  domainLayerName: Dom
```
- `myVendorName` (string): Your project vendor
- `vendorStrictMode` (bool): Indicates if you want to validate that no external vendors are imported from **Application** or **Domain** layers
- `ignoredExternalVendors` (string[]): A list of external vendors that you want to exclude from the check
- `infrastructureLayerName`(string): The name you define in your project for the **Infrastructure** layer
- `applicationLayerName`(string): The name you define in your project for the **Application** layer
- `domainLayerName`(string): The name you define in your project for the **Domain** layer


## Rules
Currently there are two rules:
- `LayersDependencyFlowRule`
- `NoExternalVendorsAllowedFromDomainRule`