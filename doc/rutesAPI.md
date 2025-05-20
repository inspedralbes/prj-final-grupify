# Documentacio API Grupify

El backend de Grupify exposa una API RESTful que segueix les convencions HTTP est�ndard. Tots els punts finals tenen un prefix `/api` i retornen respostes en format JSON.

## �ndex
- [1. Rutes d'autenticaci�](#1-rutes-dautenticaci�)
- [2. Rutes de gesti� d'usuaris](#2-rutes-de-gesti�-dusuaris)
- [3. Rutes de gesti� dels rols](#3-rutes-de-gesti�-dels-rols)
- [4. Rutes de gesti� de formularis](#4-rutes-de-gesti�-de-formularis)
- [5. Rutes de gesti� preguntes i respostes](#5-rutes-de-gesti�-preguntes-i-respostes-dels-formularis)
- [6. Rutes de gesti� sociograma i CESC](#6-rutes-de-gesti�-sociograma-i-cesc)
- [7. Rutes de gesti� cursos i divisions](#7-rutes-de-gesti�-cursos-i-divisions)
- [8. Rutes de gesti� de grups](#8-rutes-de-gesti�-de-grups)
- [9. Rutes de comentaris](#9-rutes-de-comentaris)
- [10. Rutes de notificaci�](#10-rutes-de-notificaci�)
- [11. Rutes de Bit�cola](#11-rutes-de-bit�cola)

---

## 1. Rutes d'autenticaci�

| Endpoint | M�tode | Descripci� | Requereix autenticaci� |
|:---------|:------:|:-----------|:----------------------:|
| `/api/login` | `POST` | Inici de sessi� d'usuari | No L |
| `/api/register` | `POST` | Registre d'usuari | No L |
| `/api/logout` | `POST` | Tancament de sessi� de l'usuari | S�  |
| `/api/user` | `GET` | Obt�n un usuari autenticat | S�  |
| `/api/google-login` | `POST` | Inicia la sessi� amb Google | No L |

---

## 2. Rutes de gesti� d'usuaris

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/users` | `GET` | Llistar tots els usuaris | S�  | Tots |
| `/api/users` | `POST` | Crear un usuari | S�  | admin |
| `/api/users/{id}` | `GET` | Obtenir un usuari espec�fic | S�  | Tots |
| `/api/users/{id}` | `PUT` | Actualitzar un usuari | S�  | admin |
| `/api/users/{id}` | `DELETE` | Eliminar un usuari | S�  | admin |
| `/api/users/{id}/role` | `PUT` | Assignar rol a un usuari | S�  | admin |
| `/api/users/{userId}/assign-course-division` | `POST` | Assignar curs i divisi� | S�  | admin, professor, tutor |
| `/api/users/{id}/courses` | `GET` | Obtenir cursos d'un usuari | S�  | Tots |
| `/api/get-students` | `GET` | Obtenir llista d'estudiants | S�  | Tots |
| `/api/get-teachers` | `GET` | Obtenir llista de professors | S�  | Tots |

---

## 3. Rutes de gesti� dels rols

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/roles` | `GET` | Llistar tots els rols | No L | Tots |
| `/api/roles/{id}` | `GET` | Obtenir un rol espec�fic | No L | Tots |
| `/api/roles` | `POST` | Crear un rol | S�  | admin |
| `/api/roles/{id}` | `PUT` | Actualitzar un rol | S�  | admin |
| `/api/roles/{id}` | `DELETE` | Eliminar un rol | S�  | admin |

---

## 4. Rutes de gesti� de formularis

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/forms` | `GET` | Llistar tots els formularis | No L | Tots |
| `/api/forms` | `POST` | Crear un formulari | S�  | professor, tutor, admin |
| `/api/forms/{id}` | `GET` | Obtenir un formulari espec�fic | No L | Tots |
| `/api/forms/{id}` | `PUT`/`PATCH` | Actualitzar un formulari | S�  | professor, tutor, admin |
| `/api/forms/{id}` | `DELETE` | Eliminar un formulari | S�  | professor, tutor, admin |
| `/api/forms-save` | `POST` | Desar formulari amb preguntes | S�  | professor, tutor, admin |
| `/api/forms/active` | `GET` | Obtenir formularis actius | No L | Tots |
| `/api/forms/assign-to-course-division` | `POST` | Assignar formulari a curs i divisi� | S�  | professor, tutor, admin |
| `/api/form-assignments` | `POST` | Assignar formularis a usuaris | S�  | professor, tutor, admin |
| `/api/form-assignments/teacher/{teacherId}` | `GET` | Obtenir formularis assignats per un professor | S�  | Tots |
| `/api/form-assignments/{id}` | `GET` | Obtenir detalls d'un formulari | S�  | Tots |
| `/api/form-assignments/{id}/update-count` | `POST` | Actualitzar el recompte de respostes | S�  | Tots |
| `/api/form-assignments/{id}/status` | `PATCH` | Actualitzar estat formulari | S�  | Tots |
| `/api/forms/{formId}/assignment-status` | `PATCH` | Actualitzar l'estat de l'assignaci� | S�  | Tots |
| `/api/forms/{formId}/submit-responses` | `POST` | Enviar respostes del formulari | S�  | Tots |
| `/api/forms/{formId}/questions-and-answers` | `GET` | Obtenir formulari amb preguntes i respostes | S�  | Tots |
| `/api/forms/{formId}/users` | `GET` | Obtenir usuaris que han respost | S�  | Tots |
| `/api/form-user/update-status` | `POST` | Actualitzar estat de resposta d'usuari | S�  | Tots |
| `/api/check-form-completion/{course_id}/{division_id}/{form_id}` | `GET` | Comprovar estat de finalitzaci� | S�  | Tots |

---

## 5. Rutes de gesti� preguntes i respostes dels formularis

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/questions` | `GET` | Llistar totes les preguntes | S�  | Tots |
| `/api/questions` | `POST` | Crear una pregunta | S�  | professor, tutor, admin |
| `/api/questions/{id}` | `GET` | Obtenir una pregunta espec�fica | S�  | Tots |
| `/api/questions/{id}` | `PUT` | Actualitzar una pregunta | S�  | professor, tutor, admin |
| `/api/questions/{id}` | `DELETE` | Eliminar una pregunta | S�  | professor, tutor, admin |
| `/api/answers` | `GET` | Llistar totes les respostes | S�  | professor, tutor, orientador, admin |
| `/api/answers` | `POST` | Crear una resposta | S�  | Tots |
| `/api/answers/{id}` | `GET` | Obtenir una resposta espec�fica | S�  | professor, tutor, orientador, admin |
| `/api/answers/{id}` | `PUT` | Actualitzar una resposta | S�  | professor, tutor, admin |
| `/api/answers/{id}` | `DELETE` | Eliminar una resposta | S�  | professor, tutor, admin |
| `/api/all-responses` | `GET` | Obtenir totes les respostes | S�  | professor, tutor, admin |
| `/api/questions/{questionId}/average-rating` | `GET` | Obtenir valoraci� mitjana | No L | Tots |

---

## 6. Rutes de gesti� sociograma i CESC

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/sociogram-relationships` | `GET` | Obtenir relacions sociograma | S�  | tutor, admin |
| `/api/sociogram-relationships/user/{id}` | `GET` | Obtenir relacions per usuari | S�  | tutor, admin |
| `/api/sociogram-relationships` | `POST` | Guardar relacions sociograma | S�  | Tots |
| `/api/sociogram/responses` | `POST` | Obtenir respostes per curs i divisi� | S�  | tutor, admin |
| `/api/cesc-relationships` | `GET` | Obtenir relacions CESC | S�  | tutor, admin |
| `/api/cesc-relationships/user/{id}` | `GET` | Obtenir relacions CESC per usuari | S�  | tutor, admin |
| `/api/cesc-relationships` | `POST` | Guardar relacions CESC | S�  | Tots |
| `/api/cesc/responses` | `POST` | Obtenir respostes CESC per curs i divisi� | S�  | tutor, admin |
| `/api/cesc/calcular-resultados` | `POST` | Calcular resultats CESC | S�  | tutor, orientador, admin |
| `/api/cesc/ver-resultados` | `GET` | Veure resultats CESC | S�  | tutor, orientador, admin |
| `/api/cesc/graficas-tags` | `GET` | Obtenir dades per gr�fiques | S�  | tutor, orientador, admin |

---

## 7. Rutes de gesti� cursos i divisions

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/courses` | `GET` | Llistar tots els cursos | S�  | Tots |
| `/api/courses` | `POST` | Crear un curs | S�  | admin |
| `/api/courses/{id}` | `GET` | Obtenir un curs espec�fic | S�  | Tots |
| `/api/courses/{id}` | `PUT` | Actualitzar un curs | S�  | admin |
| `/api/courses/{id}` | `DELETE` | Eliminar un curs | S�  | admin |
| `/api/divisions` | `GET` | Llistar totes les divisions | S�  | Tots |
| `/api/divisions` | `POST` | Crear una divisi� | S�  | admin |
| `/api/divisions/{id}` | `GET` | Obtenir una divisi� espec�fica | S�  | Tots |
| `/api/divisions/{id}` | `PUT` | Actualitzar una divisi� | S�  | admin |
| `/api/divisions/{id}` | `DELETE` | Eliminar una divisi� | S�  | admin |
| `/api/course-divisions` | `GET` | Obtenir divisions per curs | S�  | Tots |
| `/api/courses-with-divisions` | `GET` | Obtenir cursos amb divisions | S�  | Tots |
| `/api/course-division-user` | `POST` | Assignar usuaris a cursos i divisions | S�  | admin |

---

## 8. Rutes de gesti� de grups

| Endpoint | M�tode | Descripci� | Requereix auth | Rols permesos |
|:---------|:------:|:-----------|:-------------:|:--------------|
| `/api/groups` | `GET` | Obtenir tots els grups | S�  | - |
| `/api/groups/{id}` | `GET` | Obtenir un grup espec�fic | S�  | - |
| `/api/groups` | `POST` | Crear un nou grup | S�  | - |
| `/api/groups/{id}` | `PUT` | Actualitzar un grup existent | S�  | - |
| `/api/groups/{id}` | `DELETE` | Eliminar un grup | S�  | - |
| `/api/groups/{id}/members` | `GET` | Obtenir els membres d'un grup | S�  | - |
| `/api/groups/{id}/addStudentsToGroup` | `POST` | Afegir estudiants a un grup | S�  | - |
| `/api/groups/{groupId}/removeStudentFromGroup` | `DELETE` | Eliminar un estudiant d'un grup | S�  | - |

---

## 9. Rutes de comentaris

| Endpoint | M�tode | Descripci� | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/comments` | `GET` | Obtenir tots els comentaris | S�  |
| `/api/comments` | `POST` | Crear un comentari | S�  |
| `/api/comments/{id}` | `GET` | Obtenir un comentari espec�fic | S�  |
| `/api/comments/{id}` | `PUT` | Actualitzar un comentari | S�  |
| `/api/comments/{id}` | `DELETE` | Eliminar un comentari | S�  |
| `/api/comments/students/{studentId}` | `GET` | Obtenir comentaris d'un estudiant | S�  |
| `/api/comments/teachers/{teacherId}` | `GET` | Obtenir comentaris fets per un professor | S�  |
| `/api/groups/{idGroup}/comments` | `GET` | Obtenir comentaris d'un grup | S�  |
| `/api/groups/{idGroup}/comments` | `POST` | Afegir comentari a un grup | S�  |
| `/api/groups/{idGroup}/comments/{commentId}` | `PUT` | Actualitzar comentari d'un grup | S�  |
| `/api/groups/{idGroup}/comments/{commentId}` | `DELETE` | Eliminar comentari d'un grup | S�  |

---

## 10. Rutes de notificaci�

| Endpoint | M�tode | Descripci� | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/notifications` | `GET` | Obtenir totes les notificacions | S�  |
| `/api/notifications` | `POST` | Crear una notificaci� | S�  |
| `/api/teacher-notifications` | `GET` | Obtenir notificacions per a professors | S�  |
| `/api/notifications/{id}` | `DELETE` | Eliminar una notificaci� | S�  |

---

## 11. Rutes de Bit�cola

| Endpoint | M�tode | Descripci� | Requereix auth |
|:---------|:------:|:-----------|:-------------:|
| `/api/bitacoras` | `GET` | Obtenir totes les bit�coles | S�  |
| `/api/bitacoras` | `POST` | Crear una bit�cola | S�  |
| `/api/bitacoras/{id}` | `GET` | Obtenir una bit�cola espec�fica | S�  |
| `/api/bitacoras/{id}` | `PUT` | Actualitzar una bit�cola | S�  |
| `/api/bitacoras/{id}` | `DELETE` | Eliminar una bit�cola | S�  |
| `/api/bitacoras/{bitacoraId}/notes` | `GET` | Obtenir notes d'una bit�cola | S�  |
| `/api/bitacoras/{bitacoraId}/notes` | `POST` | Afegir nota a una bit�cola | S�  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `GET` | Obtenir una nota espec�fica | S�  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `PUT` | Actualitzar una nota | S�  |
| `/api/bitacoras/{bitacoraId}/notes/{noteId}` | `DELETE` | Eliminar una nota | S�  |
| `/api/bitacoras/{bitacoraId}/user/{userId}/notes` | `GET` | Obtenir notes d'un usuari | S�  |
| `/api/bitacoras/{groupId}/notes` | `GET` | Obtenir notes d'un grup | S�  |