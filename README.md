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


**url sv:**  `http://consultasgold.com/Servicos/CheckRg = ok`

**url api:**  `http://191.101.18.200/checkRg/api.php`

- url de acesso: `https://cnisnet.inss.gov.br/`
- problema de sessao fixado.
- proxy em uso: **arainha.hopto.org:5510**
- dependencia > api cpf: `http://191.96.28.230/instint/api_doc.php?cpf=`


#### Para alterar o proxy:
entrar no servidor **191.101.18.200**
navegar até: **/var/www/html/checkRg**
Alterar o arquivo **.env**


##### Alterar o conteudo de proxy.
```
proxy = "novoproxy"

```
