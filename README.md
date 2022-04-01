# PHP Lambda Base
A Symfony Console implementation to run as a AWS Lambda Function on a container.

## How to use?
Clone the repo and run `composer install` on its base directory.

Then, it's always a good practice to generate a different App Key (secret)
```bash
sed -i "s/^APP_SECRET=.*/APP_SECRET=$(date | md5sum| cut -f1 -d' ')/" .env
```
Now you can already build your container, tag it, and push to AWS ECR to be used on Lambda.
```bash
docker build -t local/lambda-php:1.0 .
docker tag local/lambda-php:1.0 <uid>.dkr.ecr.<aws-region>.amazonaws.com/<your-repo>:latest
docker push <uid>.dkr.ecr.<aws-region>.amazonaws.com/<your-repo>:latest
```
_* Please replace all the \<tags> with valid values that you can find in your repo_<br>
_** Also, remember that you need to be authenticated on aws ecr in the same terminal before pushing_

## How does it work?
It's actually something quite far from serverless, in the real term of the word.

AWS Lambda runs a REST API for your function, and expects your function to call this API
in order to check for events. In the positive reply of an event, it passes along the payload
so you can handle it and POST a response back to that API.

It spawns your container only when there are events to be requested from this API, so there's no waste of resources.
But this container is not only stateless, but "apparently" also read-only. Hence the configuration
of Symfony Cache being "array". When I tried to write to the disk, it failed.

Apart from writing to it's own disk, the handler is quite free to run whatever you want.
And the App Environment variables are also available.

### PHP Container Internal Flow
Using webdevops/php docker image, everything necessary is already there. So:
 * Dockerfile sets `/app/bin/console lambda:serve` as the Entrypoint;
 * "/app/src/Command/Lambda.php" executes that, instantiating the LambdaService;
 * "/app/src/Service/LambdaService.php" reads the request, handles it, and send the response.

## How to implement code on top of it?
The [LambdaService handle method](https://github.com/breier/php-lambda-base/blob/main/src/Service/LambdaService.php#L45)
has the `$payload` of the original request ready to be used as an array.

So you can simply change the contents of this method to add all the code you want.
<br>Add more classes, more dependencies, ...

Just call it from the handle and let it return a string and you should be good.
