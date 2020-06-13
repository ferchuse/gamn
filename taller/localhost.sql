

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";



CREATE TABLE IF NOT EXISTS `datosempresas` (
`cve` int(11) NOT NULL,
  `plaza` int(4) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `rfc` varchar(13) COLLATE utf8_unicode_ci NOT NULL,
  `idplaza` int(4) NOT NULL,
  `idcertificado` int(4) NOT NULL,
  `usuario` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `calle` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `numexterior` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `numinterior` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `colonia` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `localidad` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `localidad_id` int(4) NOT NULL,
  `municipio` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `codigopostal` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `timbra` tinyint(1) NOT NULL,
  `logoencabezado` tinyint(1) NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `regimen` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `por_iva_retenido` decimal(10,2) NOT NULL,
  `mod_iva_retenido` tinyint(1) NOT NULL,
  `por_isr_retenido` decimal(10,2) NOT NULL,
  `mod_isr_retenido` tinyint(1) NOT NULL,
  `decimales` tinyint(1) NOT NULL,
  `numero_lineas` tinyint(2) NOT NULL,
  `maneja_web` tinyint(1) NOT NULL,
  `maneja_callcenter` tinyint(1) NOT NULL,
  `nombre_callcenter` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `direccion_callcenter` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `carta_porte` tinyint(1) NOT NULL,
  `registro_patronal` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `repetir_rfc` tinyint(1) NOT NULL,
  `cuenta_pago` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `check_sucursal` tinyint(1) NOT NULL,
  `nombre_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `rfc_sucursal` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `calle_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `numero_sucursal` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `colonia_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `localidad_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `municipio_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `estado_sucursal` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cp_sucursal` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `horainicio` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `horafin` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `minutos` tinyint(2) NOT NULL,
  `descripcionfactura` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `idplazanomina` int(4) NOT NULL,
  `idcertificadonomina` int(4) NOT NULL,
  `usuarionomina` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `passnomina` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tipocombustible` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `call_emails` varchar(300) COLLATE utf8_unicode_ci NOT NULL,
  `fechalimiteweb` date NOT NULL,
  `mensajeinicio` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `historial` (
`cve` int(4) NOT NULL,
  `menu` int(4) NOT NULL,
  `cveaux` int(4) NOT NULL,
  `fecha` datetime NOT NULL,
  `dato` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nuevo` text COLLATE utf8_unicode_ci NOT NULL,
  `anterior` text COLLATE utf8_unicode_ci NOT NULL,
  `arreglo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `usuario` int(4) NOT NULL,
  `obs` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `menu` (
`cve` int(4) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `modulo` smallint(2) NOT NULL,
  `orden` smallint(2) NOT NULL,
  `target` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `menupadre` int(4) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;



INSERT INTO `menu` (`cve`, `nombre`, `link`, `modulo`, `orden`, `target`, `menupadre`) VALUES
(1, 'Catalogo de Usuarios', 'accesos.php', 99, 1, '', 0),
(2, 'Catalogo de Plazas', 'plazas.php', 99, 2, '', 0),
(3, 'Registro de Acceso', 'registros_sistema.php', 99, 99, '', 0),
(4, 'Cambiar Password', 'cambiopass.php', 0, 0, '', 0);


CREATE TABLE IF NOT EXISTS `plazas` (
`cve` tinyint(1) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;



INSERT INTO `plazas` (`cve`, `nombre`) VALUES
(1, 'MATRIZ');



CREATE TABLE IF NOT EXISTS `registros_sistema` (
`cve` int(4) NOT NULL,
  `usuario` int(4) NOT NULL,
  `entrada` datetime NOT NULL,
  `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `registros_sistemamov` (
`cve` int(4) NOT NULL,
  `cveacceso` int(4) NOT NULL,
  `usuario` int(4) NOT NULL,
  `menu` int(4) NOT NULL,
  `fechahora` datetime NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;





CREATE TABLE IF NOT EXISTS `usuarios` (
`cve` int(4) NOT NULL,
  `nombre` varchar(200) NOT NULL DEFAULT '',
  `usuario` varchar(20) NOT NULL DEFAULT '',
  `password` varchar(20) NOT NULL,
  `plaza` int(4) NOT NULL DEFAULT '0',
  `cerrar_sistema` char(1) NOT NULL DEFAULT '',
  `estatus` varchar(1) NOT NULL DEFAULT 'A',
  `autoriza_vales` tinyint(1) NOT NULL,
  `tipo` tinyint(1) NOT NULL,
  `empresa` int(4) NOT NULL,
  `fechacambiopass` datetime NOT NULL,
  `chat` tinyint(1) NOT NULL,
  `ide` varchar(100) NOT NULL,
  `categoria` tinyint(1) NOT NULL,
  `validar_huella` tinyint(1) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;



INSERT INTO `usuarios` (`cve`, `nombre`, `usuario`, `password`, `plaza`, `cerrar_sistema`, `estatus`, `autoriza_vales`, `tipo`, `empresa`, `fechacambiopass`, `chat`, `ide`, `categoria`, `validar_huella`) VALUES
(1, 'Administrador', 'root', 'oceano', 0, '', 'A', 0, 0, 0, '2017-04-27 10:56:45', 0, '', 0, 0);



CREATE TABLE IF NOT EXISTS `usuario_accesos` (
`cve` int(4) NOT NULL,
  `usuario` int(4) NOT NULL,
  `menu` int(4) NOT NULL,
  `acceso` smallint(1) NOT NULL,
  `plaza` int(4) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



ALTER TABLE `datosempresas`
 ADD PRIMARY KEY (`cve`);


ALTER TABLE `historial`
 ADD PRIMARY KEY (`cve`);


ALTER TABLE `menu`
 ADD PRIMARY KEY (`cve`), ADD KEY `modulo` (`modulo`);


ALTER TABLE `plazas`
 ADD PRIMARY KEY (`cve`);


ALTER TABLE `registros_sistema`
 ADD PRIMARY KEY (`cve`);


ALTER TABLE `registros_sistemamov`
 ADD PRIMARY KEY (`cve`);


ALTER TABLE `usuarios`
 ADD PRIMARY KEY (`cve`), ADD KEY `usuario` (`usuario`), ADD KEY `password` (`password`), ADD KEY `estatus` (`estatus`);


ALTER TABLE `usuario_accesos`
 ADD PRIMARY KEY (`cve`), ADD KEY `usuario` (`usuario`), ADD KEY `menu` (`menu`);



ALTER TABLE `datosempresas`
MODIFY `cve` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `historial`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `menu`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;

ALTER TABLE `plazas`
MODIFY `cve` tinyint(1) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `registros_sistema`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `registros_sistemamov`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `usuarios`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `usuario_accesos`
MODIFY `cve` int(4) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

