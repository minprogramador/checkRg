# checkRg

#### Listar configs.

```
**payload:** {
  config: "list"
}
```
**POST** -  `http://191.101.18.200/checkRg/`

-----------


#### Editar configs.

```
**payload:** {
	proxy: "http://200.180.112.2:3128",
	debug: "true",
	ip_debug: "127.0.0.1",
	config: "edit"
}
```

**POST** -  `http://191.101.18.200/checkRg/`

------------------------------------------------------------------

**Docs para tests:**

```
04414540461 = ok
07193970496 = ok
05156495460 = ok
08183919448  - ja existe uma inscricao para o cpf.
```

------------------------------------------------------------------
### Pagina de config checkRg

**url:** `http://191.101.18.200/checkRg/?token=?`

*para debugar.*
#### entrar na url de config, e colocar o ip em ip_debug, e debug deixar como true, após.

**Acessar para ver debug:** `http://191.101.18.200/checkRg/api.php`

### Para trocar proxy, basta ir na url do token demonio e trocar e testar!
# Amen.

