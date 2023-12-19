module.exports.postCreate = function(request, response, next) {
  var errors = [];

  if (!request.body.name) {
    errors.push('Name is required');
  }

  if (!request.body.phone) {
    errors.push('Phone is required');
  }

  if (errors.length) {
    response.render('users/create', {
      errors: errors,
      values: request.body
    });
    return;
  }
  next();
};
