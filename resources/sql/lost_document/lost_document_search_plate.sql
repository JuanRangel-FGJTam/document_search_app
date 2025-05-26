-- Query to search for a lost license plate in the previous version of 'lost_document_app'
SELECT
	d.id as document_id,
	d.document_number as 'plate_number',
	d.document_owner  as 'owner',
	d.registration_date,
	m.*,
	ls.name as status_name,
	p.lost_date,
	p.zipcode,
	p.municipality_api_id as municipality_id,
	p.colony_api_id as colony_id,
	p.street,
	p.description
FROM `lost_documents` d
Inner Join `document_types` t
 	ON t.id = d.document_type_id
Inner Join `misplacements` m
 	ON m.id = d.misplacement_id
INNER JOIN `place_events` p
 	ON p.misplacement_id  = p.id
LEFT OUTER JOIN `lost_statuses` ls
	ON ls.id = m.lost_status_id
WHERE d.specification like '%placa%' AND
 	(t.id = 9 OR t.name like '%otro%') AND
	1 = (CASE
 		WHEN 1 = :searchType1 THEN (CASE WHEN d.document_number like :plateNumber1 THEN 1 ELSE 0 END)
 		WHEN 2 = :searchType2 THEN (CASE WHEN m.document_number like :plateNumber2 THEN 1 ELSE 0 END)
 		ELSE 0 END)