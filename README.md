### Branch naming
Requirement described in the file
```
.validate-branch-namerc.json
```
branch name auto validate on pre-push hook using
[validate-branch-name](https://www.npmjs.com/package/validate-branch-name
) package

### Commit convention
Each commit message must follow the [commit convention](https://www.conventionalcommits.org/)

Automatic message validation on commit-msg git hook.
All setting described in the file
```
commitlint.config.js
```

## Setup local
1) Copy environments
```sh
cp .env.example .env
```
2) Run docker
```sh
docker compose up -d --build
```


### Entry points
* [http://localhost:8083](http://localhost/) - api application
* [http://localhost/horizon](http://localhost/horizon) - queue manager
