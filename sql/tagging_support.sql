ALTER TABLE agreements ENGINE = InnoDB;
ALTER TABLE agreements 
ADD FULLTEXT(title, summary, full, background, comments, processnotes);

CREATE TABLE tags (
  id INT NOT NULL AUTO_INCREMENT,
  tag VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE(tag)
);

CREATE TABLE tags_to_agreements (
  agreement_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (agreement_id, tag_id),
  FOREIGN KEY (agreement_id) REFERENCES boa.agreements(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES boa.tags(id) ON DELETE CASCADE
);

CREATE INDEX idx_tta_agreement ON tags_to_agreements(agreement_id);
CREATE INDEX idx_tta_tag ON tags_to_agreements(tag_id);
CREATE INDEX idx_tags_tag ON tags(tag);

