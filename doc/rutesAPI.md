# Documentacio API Grupify

El backend de Grupify exposa una API RESTful que segueix les convencions HTTP estandard. Tots els punts finals tenen un prefix `/api` i retornen respostes en format JSON.

## Index
- [1. Rutes d'autenticacio](#1-rutes-dautenticacio)
- [2. Rutes de gestio d'usuaris](#2-rutes-de-gestio-dusuaris)
- [3. Rutes de gestio dels rols](#3-rutes-de-gestio-dels-rols)
- [4. Rutes de gestio de formularis](#4-rutes-de-gestio-de-formularis)
- [5. Rutes de gestio preguntes i respostes](#5-rutes-de-gestio-preguntes-i-respostes-dels-formularis)
- [6. Rutes de gestio sociograma i CESC](#6-rutes-de-gestio-sociograma-i-cesc)
- [7. Rutes de gestio cursos i divisions](#7-rutes-de-gestio-cursos-i-divisions)
- [8. Rutes de gestio de grups](#8-rutes-de-gestio-de-grups)
- [9. Rutes de comentaris](#9-rutes-de-comentaris)
- [10. Rutes de notificacio](#10-rutes-de-notificacio)
- [11. Rutes de Bitacola](#11-rutes-de-bitacola)

---

## 1. Rutes d'autenticacio

| Endpoint | Metode | Descripcio | Requereix autenticacio |
|:---------|:------:|:-----------|:----------------------:|
| `/api/login` | `POST` | Inici de sessio d'usuari | No L |
| `/api/register` | `POST` | Registre d'usuari | No L |
| `/api/logout` | `POST` | Tancament de sessio de l'usuari | Si  |
| `/api/user` | `GET` | Obten un usuari autenticat | Si  |
| `/api/google-login` | `POST` | Inicia la sessio amb Google | No L |

---

## 2. Rutes de gestio d'usuaris

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/users` | `GET` | Llistar tots els usuaris | Si  | Tots |
| `/api/users` | `POST` | Crear un usuari | Si  | admin |
| `/api/users/{id}` | `GET` | Obtenir un usuari especific | Si  | Tots |
| `/api/users/{id}` | `PUT` | Actualitzar un usuari | Si  | admin |
| `/api/users/{id}` | `DELETE` | Eliminar un usuari | Si  | admin |
| `/api/users/{id}/role` | `PUT` | Assignar rol a un usuari | Si  | admin |
| `/api/users/{userId}/assign-course-division` | `POST` | Assignar curs i divisio | Si  | admin, professor, tutor |
| `/api/users/{id}/courses` | `GET` | Obtenir cursos d'un usuari | Si  | Tots |
| `/api/get-students` | `GET` | Obtenir llista d'estudiants | Si  | Tots |
| `/api/get-teachers` | `GET` | Obtenir llista de professors | Si  | Tots |

---

## 3. Rutes de gestio dels rols

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/roles` | `GET` | Llistar tots els rols | No L | Tots |
| `/api/roles/{id}` | `GET` | Obtenir un rol especific | No L | Tots |
| `/api/roles` | `POST` | Crear un rol | Si  | admin |
| `/api/roles/{id}` | `PUT` | Actualitzar un rol | Si  | admin |
| `/api/roles/{id}` | `DELETE` | Eliminar un rol | Si  | admin |

---

## 4. Rutes de gestio de formularis

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/forms` | `GET` | Llistar tots els formularis | No L | Tots |
| `/api/forms` | `POST` | Crear un formulari | Si  | professor, tutor, admin |
| `/api/forms/{id}` | `GET` | Obtenir un formulari especific | No L | Tots |
| `/api/forms/{id}` | `PUT`/`PATCH` | Actualitzar un formulari | Si  | professor, tutor, admin |
| `/api/forms/{id}` | `DELETE` | Eliminar un formulari | Si  | professor, tutor, admin |
| `/api/forms-save` | `POST` | Desar formulari amb preguntes | Si  | professor, tutor, admin |
| `/api/forms/active` | `GET` | Obtenir formularis actius | No L | Tots |
| `/api/forms/assign-to-course-division` | `POST` | Assignar formulari a curs i divisio | Si  | professor, tutor, admin |
| `/api/form-assignments` | `POST` | Assignar formularis a usuaris | Si  | professor, tutor, admin |
| `/api/form-assignments/teacher/{teacherId}` | `GET` | Obtenir formularis assignats per un professor | Si  | Tots |
| `/api/form-assignments/{id}` | `GET` | Obtenir detalls d'un formulari | Si  | Tots |
| `/api/form-assignments/{id}/update-count` | `POST` | Actualitzar el recompte de respostes | Si  | Tots |
| `/api/form-assignments/{id}/status` | `PATCH` | Actualitzar estat formulari | Si  | Tots |
| `/api/forms/{formId}/assignment-status` | `PATCH` | Actualitzar l'estat de l'assignacio | Si  | Tots |
| `/api/forms/{formId}/submit-responses` | `POST` | Enviar respostes del formulari | Si  | Tots |
| `/api/forms/{formId}/questions-and-answers` | `GET` | Obtenir formulari amb preguntes i respostes | Si  | Tots |
| `/api/forms/{formId}/users` | `GET` | Obtenir usuaris que han respost | Si  | Tots |
| `/api/form-user/update-status` | `POST` | Actualitzar estat de resposta d'usuari | Si  | Tots |
| `/api/check-form-completion/{course_id}/{division_id}/{form_id}` | `GET` | Comprovar estat de finalitzacio | Si  | Tots |

---

## 5. Rutes de gestio preguntes i respostes dels formularis

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/questions` | `GET` | Llistar totes les preguntes | Si  | Tots |
| `/api/questions` | `POST` | Crear una pregunta | Si  | professor, tutor, admin |
| `/api/questions/{id}` | `GET` | Obtenir una pregunta especifica | Si  | Tots |
| `/api/questions/{id}` | `PUT` | Actualitzar una pregunta | Si  | professor, tutor, admin |
| `/api/questions/{id}` | `DELETE` | Eliminar una pregunta | Si  | professor, tutor, admin |
| `/api/answers` | `GET` | Llistar totes les respostes | Si  | professor, tutor, orientador, admin |
| `/api/answers` | `POST` | Crear una resposta | Si  | Tots |
| `/api/answers/{id}` | `GET` | Obtenir una resposta especifica | Si  | professor, tutor, orientador, admin |
| `/api/answers/{id}` | `PUT` | Actualitzar una resposta | Si  | professor, tutor, admin |
| `/api/answers/{id}` | `DELETE` | Eliminar una resposta | Si  | professor, tutor, admin |
| `/api/all-responses` | `GET` | Obtenir totes les respostes | Si  | professor, tutor, admin |
| `/api/questions/{questionId}/average-rating` | `GET` | Obtenir valoracio mitjana | No L | Tots |

---

## 6. Rutes de gestio sociograma i CESC

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/sociogram-relationships` | `GET` | Obtenir relacions sociograma | Si  | tutor, admin |
| `/api/sociogram-relationships/user/{id}` | `GET` | Obtenir relacions per usuari | Si  | tutor, admin |
| `/api/sociogram-relationships` | `POST` | Guardar relacions sociograma | Si  | Tots |
| `/api/sociogram/responses` | `POST` | Obtenir respostes per curs i divisio | Si  | tutor, admin |
| `/api/cesc-relationships` | `GET` | Obtenir relacions CESC | Si  | tutor, admin |
| `/api/cesc-relationships/user/{id}` | `GET` | Obtenir relacions CESC per usuari | Si  | tutor, admin |
| `/api/cesc-relationships` | `POST` | Guardar relacions CESC | Si  | Tots |
| `/api/cesc/responses` | `POST` | Obtenir respostes CESC per curs i divisio | Si  | tutor, admin |
| `/api/cesc/calcular-resultados` | `POST` | Calcular resultats CESC | Si  | tutor, orientador, admin |
| `/api/cesc/ver-resultados` | `GET` | Veure resultats CESC | Si  | tutor, orientador, admin |
| `/api/cesc/graficas-tags` | `GET` | Obtenir dades per grafiques | Si  | tutor, orientador, admin |

---

## 7. Rutes de gestio cursos i divisions

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/courses` | `GET` | Llistar tots els cursos | Si  | Tots |
| `/api/courses` | `POST` | Crear un curs | Si  | admin |
| `/api/courses/{id}` | `GET` | Obtenir un curs especific | Si  | Tots |
| `/api/courses/{id}` | `PUT` | Actualitzar un curs | Si  | admin |
| `/api/courses/{id}` | `DELETE` | Eliminar un curs | Si  | admin |
| `/api/divisions` | `GET` | Llistar totes les divisions | Si  | Tots |
| `/api/divisions` | `POST` | Crear una divisio | Si  | admin |
| `/api/divisions/{id}` | `GET` | Obtenir una divisio especifica | Si  | Tots |
| `/api/divisions/{id}` | `PUT` | Actualitzar una divisio | Si  | admin |
| `/api/divisions/{id}` | `DELETE` | Eliminar una divisio | Si  | admin |
| `/api/course-divisions` | `GET` | Obtenir divisions per curs | Si  | Tots |
| `/api/courses-with-divisions` | `GET` | Obtenir cursos amb divisions | Si  | Tots |
| `/api/course-division-user` | `POST` | Assignar usuaris a cursos i divisions | Si  | admin |

---

## 8. Rutes de gestio de grups

| Endpoint | Metode | Descripcio | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/groups` | `GET` | Obtenir tots els grups | Si  | - |
| `/api/groups/{id}` | `GET` | Obtenir un grup especific | Si  | - |
| `/api/groups` | `POST` | Crear un nou grup | Si  | - |
| `/api/groups/{id}` | `PUT` | Actualitzar un grup existent | Si  | - |
| `/api/groups/{id}` | `DELETE` | Eliminar un grup | Si  | - |
| `/api/groups/{id}/members` | `GET` | Obtenir els membres d'un grup | Si  | - |
| `/api/groups/{id}/addStudentsToGroup` | `POST` | Afegir estudiants a un grup | Si  | - |
| `/api/groups/{groupId}/removeStudentFromGroup` | `DELETE` | Eliminar un estudiant d'un grup | Si  | - |

---

## 9. Rutes de comentaris

| Endpoint | Metode | Descripcio | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/comments` | `GET` | Obtenir tots els comentaris | Si  |
| `/api/comments` | `POST` | Crear un comentari | Si  |
| `/api/comments/{id}` | `GET` | Obtenir un comentari especific | Si  |
| `/api/comments/{id}` | `PUT` | Actualitzar un comentari | Si  |
| `/api/comments/{id}` | `DELETE` | Eliminar un comentari | Si  |
| `/api/comments/students/{studentId}` | `GET` | Obtenir comentaris d'un estudiant | Si  |
| `/api/comments/teachers/{teacherId}` | `GET` | Obtenir comentaris fets per un professor | Si  |
| `/api/groups/{idGroup}/comments` | `GET` | Obtenir comentaris d'un grup | Si  |
| `/api/groups/{idGroup}/comments` | `POST` | Afegir comentari a un grup | Si  |
| `/api/groups/{idGroup}/comments/{commentId}` | `PUT` | Actualitzar comentari d'un grup | Si  |
| `/api/groups/{idGroup}/comments/{commentId}` | `DELETE` | Eliminar comentari d'un grup | Si  |

---

## 10. Rutes de notificacio

| Endpoint | Metode | Descripcio | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/notifications` | `GET` | Obtenir totes les notificacions | Si  |
| `/api/notifications` | `POST` | Crear una notificacio | Si  |
| `/api/teacher-notifications` | `GET` | Obtenir notificacions per a professors | Si  |
| `/api/notifications/{id}` | `DELETE` | Eliminar una notificacio | Si  |

---

## 11. Rutes de Bitacola

| Endpoint | Metode | Descripcio | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/bitacoras` | `GET` | Obtenir totes les bitacoles | Si  |
| `/api/bitacoras` | `POST` | Crear una bitacola | Si  |
| `/api/bitacoras/{id}` | `GET` | Obtenir una bitacola especifica | Si  |
| `/api/bitacoras/{id}` | `PUT` | Actualitzar una bitacola | Si  |
| `/api/bitacoras/{id}` | `DELETE` | Eliminar una bitacola | Si  |
| `/api/bitacoras/{bitacoraId}/notes` | `GET` | Obtenir notes d'una bitacola | Si  |
| `/api/bitacoras/{bitacoraId}/notes` | `POST` | Afegir nota a una bitacola | Si  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `GET` | Obtenir una nota especifica | Si  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `PUT` | Actualitzar una nota | Si  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `DELETE` | Eliminar una nota | Si  |
| `/api/bitacoras/{bitacoraId}/user/{userId}/notes` | `GET` | Obtenir notes d'un usuari | Si  |
| `/api/bitacoras/{groupId}/notes` | `GET` | Obtenir notes d'un grup | Si  |