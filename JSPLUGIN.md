# Adquesto JavaScript Plugin

## Events

The Adquesto plugin emits events related to the change of state. Events can be used for integration.

### Available events

* `adquesto.ready` - triggered when library loaded and ready
* `adquesto.emissionskip` - triggered when there is no quest to display
* `adquesto.emissionstart` - triggered after site blur, directly before quest display
* `adquesto.emissionend` - triggered after site unblur, so after correct answer
* `adquesto.subscriptiontoggle` - triggered when displaying the subscription board

### JavaScript events handling example

```javascript
document.addEventListener('adquesto.ready', function(event) {
  console.log('adquesto.ready', event);
}, true);
document.addEventListener('adquesto.emissionstart', function(event) {
  console.log('adquesto.emissionstart', event);
}, true);
document.addEventListener('adquesto.emissionend', function(event) {
  console.log('adquesto.emissionend', event);
}, true);
document.addEventListener('adquesto.emissionskip', function(event) {
  console.log('adquesto.emissionskip', event);
}, true);
document.addEventListener('adquesto.subscriptiontoggle', function(event) {
  console.log('adquesto.subscriptiontoggle', event);
}, true);
```