<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Document_number_model extends MY_Model
{
  /**
   * Generate the next document number.
   *
   * Examples:
   * SO-2026-000001
   * GRN-2026-000001
   * IA-2026-000001
   *
   * The number is generated atomically inside the current
   * database transaction.
   */
  public function generate($documentCode)
  {
    $documentCode = strtoupper(trim($documentCode));

    if ($documentCode === '') {
      throw new Exception(
        'Document code is required.'
      );
    }

    /**
     * Lock the numbering configuration row.
     *
     * This prevents two users from receiving
     * the same document number simultaneously.
     */
    $row = $this->db
      ->query(
        "SELECT *
           FROM m_document_numbering
          WHERE document_code = ?
            AND is_active = TRUE
          FOR UPDATE",
        [$documentCode]
      )
      ->row();

    if (!$row) {
      throw new Exception(
        "Document numbering configuration not found for {$documentCode}."
      );
    }

    $currentYear = (int)date('Y');

    /**
     * Determine the number to use.
     */
    $nextNumber = (int)$row->next_number;

    /**
     * If YEAR is part of the series and we have
     * entered a new year, automatically reset the
     * sequence to 1.
     */
    if (
      (bool)$row->include_year &&
      (
        empty($row->current_year) ||
        (int)$row->current_year !== $currentYear
      )
    ) {
      $nextNumber = 1;
    }

    /**
     * Build the year portion.
     */
    $year = '';

    if ((bool)$row->include_year) {

      switch (strtoupper($row->year_format)) {

        case 'YY':
          $year = date('y');
          break;

        case 'YYYY':
        default:
          $year = date('Y');
          break;
      }
    }

    /**
     * Zero-pad the sequence.
     */
    $number = str_pad(
      (string)$nextNumber,
      (int)$row->number_length,
      '0',
      STR_PAD_LEFT
    );

    /**
     * Build document number.
     *
     * Example:
     * SO-2026-000001
     */
    $parts = [];

    if (trim($row->prefix) !== '') {
      $parts[] = trim($row->prefix);
    }

    if ($year !== '') {
      $parts[] = $year;
    }

    $parts[] = $number;

    $documentNumber = implode(
      $row->separator,
      $parts
    );

    /**
     * Advance the sequence.
     *
     * If YEAR is enabled, remember the current year.
     */
    $nextNumber++;

    $this->db
      ->where('id', $row->id)
      ->update(
        'm_document_numbering',
        [
          'current_year' => $row->include_year
            ? $currentYear
            : $row->current_year,

          'next_number' => $nextNumber,

          'updated_by' => $this->session->userdata('user_id'),
          'updated_on' => date('Y-m-d H:i:s')
        ]
      );

    if (!$this->db->affected_rows()) {
      throw new Exception(
        "Unable to update numbering sequence for {$documentCode}."
      );
    }

    return $documentNumber;
  }

  public function getAll()
  {
    return $this->db
      ->order_by('document_name', 'ASC')
      ->get('m_document_numbering')
      ->result();
  }

  public function update($id, $data)
  {
    $id = (int)$id;

    $prefix = trim($data['prefix'] ?? '');

    $includeYear = !empty($data['include_year']);

    $yearFormat = strtoupper(
      trim($data['year_format'] ?? 'YYYY')
    );

    $separator = $data['separator'] ?? '-';

    $numberLength = (int)($data['number_length'] ?? 6);

    $nextNumber = (int)($data['next_number'] ?? 1);

    if ($id <= 0) {
      throw new Exception(
        'Invalid document numbering record.'
      );
    }

    if ($numberLength <= 0) {
      throw new Exception(
        'Number length must be greater than zero.'
      );
    }

    if ($nextNumber <= 0) {
      throw new Exception(
        'Next number must be greater than zero.'
      );
    }

    if (!in_array($yearFormat, ['YYYY', 'YY'], TRUE)) {
      throw new Exception(
        'Invalid year format.'
      );
    }

    $exists = $this->db
      ->where('id', $id)
      ->count_all_results('m_document_numbering');

    if ($exists == 0) {
      throw new Exception(
        'Document numbering configuration not found.'
      );
    }

    $result = $this->db
      ->where('id', $id)
      ->update(
        'm_document_numbering',
        [
          'prefix'       => $prefix !== '' ? $prefix : NULL,
          'include_year' => $includeYear,
          'year_format'  => $yearFormat,
          'separator'    => $separator,
          'number_length'=> $numberLength,
          'next_number'  => $nextNumber,
          'is_active'    => !empty($data['is_active']),
          'updated_by'   => $this->session->userdata('user_id'),
          'updated_on'   => date('Y-m-d H:i:s')
        ]
      );

    if (!$result) {
      throw new Exception(
        'Unable to update document numbering.'
      );
    }

    return TRUE;
  }
}