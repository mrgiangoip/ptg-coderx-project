var	express = require('express');
var multer = require('multer');

var controller = require('../controllers/user.controller');
var validate = require('../validate/user.validate');
var authMiddleware = require('../middlewares/auth.middleware');

var upload = multer({ dest: "./public/uploads/"})

var router = express.Router();


router.get('/', authMiddleware.requireAuth, controller.index);

router.get('/cookie', function(request, response, next){
 response.cookie('user-id', 12345);
 response.send('hello');
});

router.get('/search', controller.search);

router.get('/create', controller.create);

router.get('/:id', controller.get);

router.post('/create', 
	upload.single('avatar'), 
	validate.postCreate, 
	controller.postCreate
	);

module.exports = router;